<?php

namespace Plugins\QuizletImport\Controllers;

use App\Models\Item;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class QuizletImportController extends Controller
{
    public function show()
    {
        return view('quizlet-import::import');
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
}
