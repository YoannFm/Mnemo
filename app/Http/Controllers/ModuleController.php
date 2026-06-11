<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\Emoji;
use App\Models\Module;
use App\Models\ModuleRating;
use App\Models\ModuleRatingDeletion;
use App\Models\ModuleRatingReaction;
use App\Models\ModuleRatingReply;
use App\Models\ModuleRatingReplyReport;
use App\Models\ModuleRatingReport;
use App\Models\ModuleReport;
use App\Models\Mute;
use App\Models\Progress;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Contrôleur CRUD des modules.
 * Gère la création, l'affichage, la modification et la suppression des modules
 * appartenant à l'utilisateur connecté.
 */
class ModuleController extends Controller
{
    /**
     * Affiche la liste des modules de l'utilisateur connecté.
     * Triés du plus récent au plus ancien.
     */
    public function index(Request $request)
    {
        $query = Auth::user()->modules()->with('tags')->withCount('items')->latest();

        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $modules = $query->paginate(12)->withQueryString();

        $trashedCount = Module::onlyTrashed()->where('owner_id', Auth::id())->count();

        return view('modules.index', compact('modules', 'search', 'trashedCount'));
    }

    /**
     * Affiche le formulaire de création d'un nouveau module.
     */
    public function create()
    {
        $user = Auth::user();
        if ($user->role && !$user->role->can_create_module && !$user->is_admin) {
            abort(403, 'Vous n\'avez pas la permission de creer des modules.');
        }
        return view('modules.create');
    }

    /**
     * Enregistre un nouveau module en base de données.
     * Valide les données du formulaire avant insertion.
     */
    public function store(Request $request)
    {
        // Validation des champs du formulaire
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public'   => 'nullable|boolean',
        ]);

        $user = Auth::user();
        if ($user->role && !$user->role->can_create_module && !$user->is_admin) {
            abort(403, 'Vous n\'avez pas la permission de creer des modules.');
        }

        // Création du module lié à l'utilisateur connecté
        $user->modules()->create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_public'   => $request->boolean('is_public'),
        ]);

        return redirect()->route('modules.index')
            ->with('success', 'Module créé avec succès !');
    }

    /**
     * Affiche le détail d'un module avec la liste de ses items.
     * Vérifie que l'utilisateur est propriétaire ou que le module est public.
     */
    public function show(Module $module)
    {
        // Un utilisateur non connecté ou tiers ne peut voir qu'un module public
        $this->authorizeView($module);

        $items = $module->items()->paginate(20);

        $avgRating   = $module->ratings()->avg('rating');
        $ratingCount = $module->ratings()->count();
        $userRating  = Auth::check()
            ? $module->ratings()->where('user_id', Auth::id())->first()
            : null;

        $isMuted = Auth::check() && Mute::where('user_id', Auth::id())
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();

        // Réactions par avis : [rating_id => [slug => count]]
        // Réactions de l'utilisateur : [rating_id => [slug, ...]]
        $allRatingsWithData = $module->ratings()
            ->with(['user', 'replies.user', 'reactions'])
            ->whereNotNull('comment')
            ->latest()
            ->get();

        $ratingReactions  = [];
        $userRatingReacts = [];
        foreach ($allRatingsWithData as $r) {
            $ratingReactions[$r->id] = $r->reactions
                ->groupBy('emoji')
                ->map(fn($g) => $g->count())
                ->toArray();
            $userRatingReacts[$r->id] = Auth::check()
                ? $r->reactions->where('user_id', Auth::id())->pluck('emoji')->toArray()
                : [];
        }

        $sharedExams = Auth::id() === $module->owner_id
            ? $module->sharedExams()->withCount('attempts')->latest()->get()
            : collect();

        // Progression par item pour l'utilisateur connecté
        $progressMap = Auth::check()
            ? Progress::where('user_id', Auth::id())
                      ->whereIn('item_id', $module->items()->pluck('id'))
                      ->get()
                      ->keyBy('item_id')
            : collect();

        $failedItemsCount = $progressMap->filter(fn($p) => $p->fail_count > 0 && $p->streak < 3)->count();

        return view('modules.show', compact(
            'module', 'items', 'avgRating', 'ratingCount', 'userRating', 'isMuted',
            'allRatingsWithData', 'ratingReactions', 'userRatingReacts', 'sharedExams',
            'progressMap', 'failedItemsCount'
        ));
    }

    /**
     * Affiche le formulaire d'édition d'un module.
     * Seul le propriétaire peut modifier son module.
     */
    public function edit(Module $module)
    {
        $this->authorizeOwner($module);

        return view('modules.edit', compact('module'));
    }

    /**
     * Enregistre les modifications apportées à un module existant.
     */
    public function update(Request $request, Module $module)
    {
        $this->authorizeOwner($module);

        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string|max:1000',
            'is_public'         => 'nullable|boolean',
            'allow_duplication' => 'nullable|boolean',
            'new_owner_email'   => 'nullable|email|exists:users,email',
        ]);

        $oldOwnerId = $module->owner_id;
        $newOwnerId = $module->owner_id;

        if (!empty($validated['new_owner_email'])) {
            $newOwner = \App\Models\User::where('email', $validated['new_owner_email'])->first();
            if ($newOwner->id === $module->owner_id) {
                return back()->withErrors(['new_owner_email' => 'Cet utilisateur est déjà le propriétaire du module.'])->withInput();
            }
            $newOwnerId = $newOwner->id;
        }

        $module->update([
            'title'             => $validated['title'],
            'description'       => $validated['description'] ?? null,
            'is_public'         => $request->boolean('is_public'),
            'allow_duplication' => $request->boolean('allow_duplication'),
            'owner_id'          => $newOwnerId,
        ]);

        if ($newOwnerId !== $oldOwnerId) {
            LogHelper::log('transferred_module', 'module', $module->id, [
                'from_user_id'  => $oldOwnerId,
                'to_user_id'    => $newOwnerId,
                'module_title'  => $module->title,
            ], 'warning');

            UserNotification::create([
                'user_id' => $newOwnerId,
                'title'   => 'Module transfere',
                'message' => 'Le module "' . $module->title . '" vous a ete transfere.',
                'type'    => 'info',
            ]);
        }

        return redirect()->route('modules.show', $module)
            ->with('success', 'Module mis à jour avec succès !');
    }

    /**
     * Supprime un module et tous ses items (cascade définie en BDD).
     * Seul le propriétaire peut supprimer son module.
     */
    public function destroy(Module $module)
    {
        $this->authorizeOwner($module);

        $module->delete();

        return redirect()->route('modules.index')
            ->with('success', 'Module supprimé.');
    }

    /**
     * Affiche la corbeille des modules supprimés de l'utilisateur connecté.
     */
    public function trash()
    {
        $modules = Module::onlyTrashed()
            ->where('owner_id', Auth::id())
            ->withCount('items')
            ->latest('deleted_at')
            ->get();

        return view('modules.trash', compact('modules'));
    }

    /**
     * Restaure un module supprimé (soft delete).
     */
    public function restore(int $id)
    {
        Module::onlyTrashed()
            ->where('id', $id)
            ->where('owner_id', Auth::id())
            ->firstOrFail()
            ->restore();

        return redirect()->route('modules.trash')
            ->with('success', 'Module restauré avec succès.');
    }

    /**
     * Supprime définitivement un module de la corbeille.
     */
    public function forceDelete(int $id)
    {
        Module::onlyTrashed()
            ->where('id', $id)
            ->where('owner_id', Auth::id())
            ->firstOrFail()
            ->forceDelete();

        return redirect()->route('modules.trash')
            ->with('success', 'Module supprimé définitivement.');
    }

    /**
     * Duplique un module public dans l'espace de l'utilisateur connecté.
     * Copie le module et tous ses items (sans les photos).
     */
    public function preview(Module $module)
    {
        if (!$module->is_public && $module->owner_id !== Auth::id() && !Auth::user()?->is_admin) {
            abort(403);
        }

        $items = $module->items()->select('name_fr', 'name_alt', 'function_text', 'photo_path')->get()->map(function ($item) {
            return [
                'name_fr'       => $item->name_fr,
                'name_alt'       => $item->name_alt,
                'function_text' => $item->function_text,
                'photo_url'     => $item->photo_url,
            ];
        });

        return response()->json([
            'title'       => $module->title,
            'description' => $module->description,
            'items'       => $items,
        ]);
    }

    public function resetProgress(Module $module)
    {
        $itemIds = $module->items()->pluck('id');

        Progress::where('user_id', Auth::id())
            ->whereIn('item_id', $itemIds)
            ->delete();

        return redirect()->route('modules.show', $module)
            ->with('success', 'Progression réinitialisée.');
    }

    public function duplicate(Module $module)
    {
        if (!$module->is_public && $module->owner_id !== Auth::id() && !Auth::user()?->is_admin) {
            abort(403, 'Ce module est privé.');
        }

        $user = Auth::user();
        if (!$module->allow_duplication && $module->owner_id !== $user->id && !$user->is_admin) {
            return redirect()->back()->with('error', 'Le créateur de ce module n\'autorise pas la duplication.');
        }

        // Créer une copie du module (privée par défaut)
        $copy = Auth::user()->modules()->create([
            'title'       => $module->title . ' (copie)',
            'description' => $module->description,
            'is_public'   => false,
        ]);

        foreach ($module->items as $item) {
            $newPhotoPath = null;
            if ($item->photo_path && Storage::disk('public')->exists($item->photo_path)) {
                $newPhotoPath = 'items/' . Str::uuid() . '.jpg';
                Storage::disk('public')->copy($item->photo_path, $newPhotoPath);
            }
            $copy->items()->create([
                'name_fr'       => $item->name_fr,
                'name_alt'      => $item->name_alt,
                'function_text' => $item->function_text,
                'photo_path'    => $newPhotoPath,
            ]);
        }

        return redirect()->route('modules.show', $copy)
            ->with('success', 'Module dupliqué dans votre espace ! Vous pouvez maintenant l\'enrichir.');
    }

    /**
     * Signale un module public.
     * Un utilisateur ne peut signaler qu'une fois le même module.
     */
    public function report(Request $request, Module $module)
    {
        if (!$module->is_public) {
            return response()->json(['status' => 'error', 'message' => 'Ce module n\'est pas public.'], 403);
        }

        if ($module->owner_id === Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Vous ne pouvez pas signaler votre propre module.'], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|in:spam,inappropriate,copyright,other',
            'note'   => 'nullable|string|max:500',
        ]);

        $already = ModuleReport::where('module_id', $module->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($already) {
            return response()->json(['status' => 'already_reported']);
        }

        ModuleReport::create([
            'module_id' => $module->id,
            'user_id'   => Auth::id(),
            'reason'    => $validated['reason'],
            'note'      => $validated['note'] ?? null,
            'status'    => 'pending',
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function rate(Request $request, Module $module)
    {
        $this->authorizeView($module);

        $isMuted = Mute::where('user_id', Auth::id())
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();

        if ($isMuted) {
            return response()->json(['status' => 'muted', 'message' => 'Vous êtes muté et ne pouvez pas poster d\'avis.'], 403);
        }

        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        ModuleRating::updateOrCreate(
            ['user_id' => Auth::id(), 'module_id' => $module->id],
            ['rating' => $validated['rating'], 'comment' => $validated['comment'] ?? null]
        );

        return response()->json(['status' => 'ok']);
    }

    public function deleteRating(ModuleRating $rating)
    {
        if ($rating->user_id !== Auth::id() && !Auth::user()?->is_admin) {
            abort(403);
        }

        ModuleRatingDeletion::create([
            'user_id'    => $rating->user_id,
            'module_id'  => $rating->module_id,
            'type'       => 'rating',
            'rating'     => $rating->rating,
            'content'    => $rating->comment,
            'deleted_by' => 'user',
        ]);

        $rating->delete();

        return response()->json(['status' => 'ok']);
    }

    public function reactToRating(Request $request, ModuleRating $rating)
    {
        $isMuted = Mute::where('user_id', Auth::id())
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();

        if ($isMuted) {
            return response()->json(['status' => 'muted', 'message' => 'Vous êtes muté et ne pouvez pas réagir.'], 403);
        }

        $emoji = $request->input('emoji');

        if (!Emoji::where('slug', $emoji)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Emoji invalide.'], 422);
        }

        $existing = ModuleRatingReaction::where([
            'module_rating_id' => $rating->id,
            'user_id'          => Auth::id(),
            'emoji'            => $emoji,
        ])->first();

        if ($existing) {
            $existing->delete();
            $active = false;
        } else {
            ModuleRatingReaction::create([
                'module_rating_id' => $rating->id,
                'user_id'          => Auth::id(),
                'emoji'            => $emoji,
            ]);
            $active = true;
        }

        $count = ModuleRatingReaction::where('module_rating_id', $rating->id)
            ->where('emoji', $emoji)
            ->count();

        return response()->json(['active' => $active, 'count' => $count]);
    }

    public function replyToRating(Request $request, ModuleRating $rating)
    {
        $isMuted = Mute::where('user_id', Auth::id())
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();

        if ($isMuted) {
            return response()->json(['status' => 'muted', 'message' => 'Vous êtes muté et ne pouvez pas répondre.'], 403);
        }

        $validated = $request->validate(['content' => 'required|string|max:1000']);

        $reply = ModuleRatingReply::create([
            'module_rating_id' => $rating->id,
            'user_id'          => Auth::id(),
            'content'          => $validated['content'],
        ]);

        $reply->load('user');

        return response()->json([
            'status'  => 'ok',
            'id'      => $reply->id,
            'author'  => $reply->user->name,
            'content' => $reply->content,
            'date'    => $reply->created_at->diffForHumans(),
            'mine'    => true,
            'delete_url' => route('modules.ratings.replies.destroy', $reply),
        ]);
    }

    public function reportRatingReply(Request $request, ModuleRatingReply $reply)
    {
        if ($reply->user_id === Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Vous ne pouvez pas signaler votre propre réponse.'], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|in:spam,inappropriate,harassment,other',
            'note'   => 'nullable|string|max:500',
        ]);

        $already = ModuleRatingReplyReport::where('module_rating_reply_id', $reply->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($already) {
            return response()->json(['status' => 'already_reported']);
        }

        ModuleRatingReplyReport::create([
            'module_rating_reply_id' => $reply->id,
            'user_id'                => Auth::id(),
            'reason'                 => $validated['reason'],
            'note'                   => $validated['note'] ?? null,
            'status'                 => 'pending',
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function deleteRatingReply(ModuleRatingReply $reply)
    {
        if ($reply->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }

        ModuleRatingDeletion::create([
            'user_id'    => $reply->user_id,
            'module_id'  => $reply->moduleRating?->module_id,
            'type'       => 'reply',
            'content'    => $reply->content,
            'deleted_by' => Auth::user()->is_admin && Auth::id() !== $reply->user_id ? 'admin' : 'user',
        ]);

        $reply->delete();
        return response()->json(['status' => 'ok']);
    }

    public function reportRating(Request $request, ModuleRating $rating)
    {
        if ($rating->user_id === Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Vous ne pouvez pas signaler votre propre avis.'], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|in:spam,inappropriate,harassment,other',
            'note'   => 'nullable|string|max:500',
        ]);

        $already = ModuleRatingReport::where('module_rating_id', $rating->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($already) {
            return response()->json(['status' => 'already_reported']);
        }

        ModuleRatingReport::create([
            'module_rating_id' => $rating->id,
            'user_id'          => Auth::id(),
            'reason'           => $validated['reason'],
            'note'             => $validated['note'] ?? null,
            'status'           => 'pending',
        ]);

        return response()->json(['status' => 'ok']);
    }

    // ─────────────────────────────────────────────
    // Méthodes privées de vérification d'accès
    // ─────────────────────────────────────────────

    /**
     * Vérifie que l'utilisateur connecté est le propriétaire du module.
     * Lance une exception 403 si ce n'est pas le cas.
     */
    private function authorizeOwner(Module $module): void
    {
        if ($module->owner_id !== Auth::id() && !Auth::user()?->is_admin) {
            abort(403, 'Action non autorisée.');
        }
    }

    /**
     * Vérifie que l'utilisateur peut voir ce module :
     * soit il en est le propriétaire, soit le module est public, soit c'est un admin.
     */
    private function authorizeView(Module $module): void
    {
        if (!$module->is_public && $module->owner_id !== Auth::id() && !Auth::user()?->is_admin) {
            abort(403, 'Ce module est privé.');
        }
    }
}
