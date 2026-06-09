<?php

namespace Plugins\QuizletImport\Controllers;

use App\Models\Item;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class QuizletImportController extends Controller
{
    public function show()
    {
        return view('quizlet-import::import');
    }

    public function importFromUrl(Request $request)
    {
        $request->validate([
            'quizlet_url' => 'required|url',
            'module_name' => 'nullable|string|max:255',
        ]);

        $url = $request->quizlet_url;

        // Normaliser l'URL
        if (!str_contains($url, 'quizlet.com')) {
            return back()->withInput()->with('error', 'L\'URL doit être une page Quizlet valide.');
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                    'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'fr-FR,fr;q=0.9,en;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Cache-Control'   => 'no-cache',
                    'Referer'         => 'https://quizlet.com/',
                ])
                ->get($url);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Impossible d\'accéder à Quizlet : ' . $e->getMessage());
        }

        if (!$response->successful()) {
            return back()->withInput()->with('error', 'Quizlet a refusé la requête (erreur ' . $response->status() . '). Utilise l\'import manuel à la place.');
        }

        $html = $response->body();

        // Extraire __NEXT_DATA__
        if (!preg_match('/<script id="__NEXT_DATA__"[^>]*>(.*?)<\/script>/s', $html, $matches)) {
            return back()->withInput()->with('error', 'Impossible d\'extraire les données du set. Le format de la page a peut-être changé.');
        }

        $json = json_decode($matches[1], true);
        $cards = $this->extractCards($json);

        if (empty($cards)) {
            return back()->withInput()->with('error', 'Aucune carte trouvée dans ce set. Essaie l\'import manuel.');
        }

        // Titre du module
        $title = $request->module_name
            ?: ($json['props']['pageProps']['dehydratedReduxStateKey'] ?? null
                ? null
                : $this->extractTitle($json))
            ?: 'Import Quizlet';

        $module = Module::create([
            'title'     => $title,
            'owner_id'  => Auth::id(),
            'is_public' => false,
        ]);

        foreach ($cards as [$term, $definition]) {
            Item::create([
                'module_id'     => $module->id,
                'name_fr'       => $term,
                'function_text' => $definition,
            ]);
        }

        return redirect()->route('modules.show', $module)
            ->with('success', count($cards) . ' items importés depuis Quizlet.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'module_name' => 'required|string|max:255',
            'content'     => 'required|string',
            'separator'   => 'required|in:tab,semicolon,pipe',
        ]);

        $sep = match($request->separator) {
            'tab'       => "\t",
            'semicolon' => ';',
            'pipe'      => '|',
        };

        $lines = preg_split('/\r?\n/', trim($request->content));
        $items = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            $parts = explode($sep, $line, 2);
            if (count($parts) < 2) continue;
            $items[] = [trim($parts[0]), trim($parts[1])];
        }

        if (empty($items)) {
            return back()->withInput()->with('error', 'Aucun item valide trouvé. Vérifiez le séparateur.');
        }

        $module = Module::create([
            'title'     => $request->module_name,
            'owner_id'  => Auth::id(),
            'is_public' => false,
        ]);

        foreach ($items as [$term, $definition]) {
            Item::create([
                'module_id'     => $module->id,
                'name_fr'       => $term,
                'function_text' => $definition,
            ]);
        }

        return redirect()->route('modules.show', $module)
            ->with('success', count($items) . ' items importés depuis Quizlet.');
    }

    private function extractCards(array $json): array
    {
        $cards = [];

        // Chercher dans plusieurs emplacements possibles du JSON Next.js
        $paths = [
            ['props', 'pageProps', 'studyableData', 'studiableItems'],
            ['props', 'pageProps', 'studyModeData', 'cards'],
            ['props', 'pageProps', 'initialData', 'set', 'terms'],
            ['props', 'pageProps', 'set', 'terms'],
        ];

        $items = null;
        foreach ($paths as $path) {
            $node = $json;
            foreach ($path as $key) {
                if (!isset($node[$key])) { $node = null; break; }
                $node = $node[$key];
            }
            if (is_array($node) && count($node) > 0) {
                $items = $node;
                break;
            }
        }

        // Fallback : chercher récursivement un tableau avec word/definition
        if (!$items) {
            $items = $this->findTermsRecursive($json);
        }

        if (!$items) return [];

        foreach ($items as $item) {
            $term = $item['word'] ?? $item['term'] ?? $item['front'] ?? null;
            $def  = $item['definition'] ?? $item['back'] ?? $item['description'] ?? null;
            if ($term && $def) {
                $cards[] = [strip_tags($term), strip_tags($def)];
            }
        }

        return $cards;
    }

    private function findTermsRecursive(array $data, int $depth = 0): ?array
    {
        if ($depth > 8) return null;

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Vérifier si c'est un tableau de cartes
                if (isset($value[0]) && is_array($value[0])) {
                    $first = $value[0];
                    if (isset($first['word']) || isset($first['term']) || isset($first['definition'])) {
                        return $value;
                    }
                }
                $result = $this->findTermsRecursive($value, $depth + 1);
                if ($result) return $result;
            }
        }

        return null;
    }

    private function extractTitle(array $json): ?string
    {
        $paths = [
            ['props', 'pageProps', 'set', 'title'],
            ['props', 'pageProps', 'studyableData', 'set', 'title'],
            ['props', 'pageProps', 'initialData', 'set', 'title'],
        ];

        foreach ($paths as $path) {
            $node = $json;
            foreach ($path as $key) {
                if (!isset($node[$key])) { $node = null; break; }
                $node = $node[$key];
            }
            if (is_string($node)) return $node;
        }

        return null;
    }
}
