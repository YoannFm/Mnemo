<?php
namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Support\Facades\Auth;
use League\CommonMark\CommonMarkConverter;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)->where('is_enabled', true)->firstOrFail();

        if ($page->isRestricted()) {
            if (!Auth::check()) {
                return redirect()->route('login');
            }
            $user = Auth::user();
            if (!$user->is_admin && !$page->roles->contains($user->role_id)) {
                abort(403);
            }
        }

        $converter = new CommonMarkConverter(['html_input' => 'strip', 'allow_unsafe_links' => false]);
        $markdownContent = $converter->convert($page->content);

        return view('pages.show', compact('page', 'markdownContent'));
    }
}
