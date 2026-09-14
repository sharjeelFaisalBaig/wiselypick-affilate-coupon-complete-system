<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MenuController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        Menu::ensureFixedMenusExist($region, Menu::SCOPE_GLOBAL);
        Menu::ensureFixedMenusExist($region, Menu::SCOPE_BLOG);

        $slotOrder = array_keys(Menu::FIXED_SLOTS);
        $menusByScope = Menu::where('region_id', $region->id)->withCount('items')->get()
            ->sortBy(fn ($menu) => array_search($menu->slot, $slotOrder))
            ->groupBy('scope');

        return view('admin.menus.index', compact('menusByScope'));
    }

    public function edit(Request $request, Menu $menu): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $menu->region_id);

        return view('admin.menus.edit', [
            'menu' => $menu,
            'items' => $menu->items,
        ]);
    }

    public function storeItem(Request $request, Menu $menu): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $menu->region_id);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:2048'],
            'target' => ['required', 'in:same_tab,new_tab'],
        ]);

        $data['sort_order'] = ($menu->items()->max('sort_order') ?? 0) + 1;

        $menu->items()->create($data);

        return redirect()->route('admin.menus.edit', $menu)->with('status', 'Menu item added.');
    }

    public function destroyItem(Request $request, Menu $menu, MenuItem $item): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $menu->region_id);
        abort_if($item->menu_id !== $menu->id, 404);

        $item->delete();

        return redirect()->route('admin.menus.edit', $menu)->with('status', 'Menu item removed.');
    }

    public function reorderItems(Request $request, Menu $menu): Response
    {
        $this->abortUnlessOwnedByActiveRegion($request, $menu->region_id);

        $ids = $request->validate(['ids' => ['required', 'array']])['ids'];

        foreach ($ids as $index => $id) {
            MenuItem::where('id', $id)->where('menu_id', $menu->id)->update(['sort_order' => $index + 1]);
        }

        return response()->noContent();
    }
}
