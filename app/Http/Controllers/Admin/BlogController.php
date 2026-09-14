<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Region;
use App\Support\BlogContentProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $blogs = Blog::where('region_id', $region->id)->with('blogCategory')->orderBy('sort_order')->get();

        return view('admin.blogs.index', compact('blogs'));
    }

    public function create(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        return view('admin.blogs.form', [
            'blog' => new Blog(),
            'blogCategories' => BlogCategory::where('region_id', $region->id)->orderBy('name')->get(),
            'otherBlogs' => Blog::where('region_id', $region->id)->orderBy('title')->get(),
            'selectedRelatedBlogs' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;
        $data['slug'] = $data['slug'] ?: $this->uniqueSlug($data['title'], $region->id);
        $data['sort_order'] = (Blog::where('region_id', $region->id)->max('sort_order') ?? 0) + 1;

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('blogs', 'public');
        }

        $blog = Blog::create($data);
        $this->syncRelatedBlogs($request, $blog);

        return redirect()->route('admin.blogs.index')->with('status', 'Blog post created.');
    }

    public function edit(Request $request, Blog $blog): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $blog->region_id);

        return view('admin.blogs.form', [
            'blog' => $blog,
            'blogCategories' => BlogCategory::where('region_id', $blog->region_id)->orderBy('name')->get(),
            'otherBlogs' => Blog::where('region_id', $blog->region_id)->where('id', '!=', $blog->id)->orderBy('title')->get(),
            'selectedRelatedBlogs' => $blog->relatedBlogs->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Blog $blog): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $blog->region_id);

        $data = $this->validated($request, $blog);
        if ($data['slug']) {
            // Admin explicitly typed a slug — already validated unique below.
        } elseif ($data['title'] !== $blog->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $blog->region_id, $blog->id);
        } else {
            unset($data['slug']);
        }

        if ($request->hasFile('featured_image')) {
            if ($blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->store('blogs', 'public');
        }

        $blog->update($data);
        $this->syncRelatedBlogs($request, $blog);

        return redirect()->route('admin.blogs.index')->with('status', 'Blog post updated.');
    }

    public function destroy(Request $request, Blog $blog): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $blog->region_id);

        if ($blog->featured_image) {
            Storage::disk('public')->delete($blog->featured_image);
        }

        $blog->delete();

        return redirect()->route('admin.blogs.index')->with('status', 'Blog post deleted.');
    }

    public function reorder(Request $request): Response
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate(['ids' => ['required', 'array']]);

        foreach ($data['ids'] as $index => $id) {
            Blog::where('id', $id)->where('region_id', $region->id)->update(['sort_order' => $index + 1]);
        }

        return response()->noContent();
    }

    /** See Admin\StoreController::notReservedPrefix() for the rationale. */
    private function notReservedPrefix(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (! $value) {
                return;
            }

            $segments = explode('/', strtolower(trim($value, '/')));
            if (in_array($segments[0], ['p', 'suggest', 'go', 'contact'], true)) {
                $fail("The prefix can't start with \"{$segments[0]}\" — that path is already used elsewhere on the site.");
            }
            if (($segments[0] ?? null) === 'exclusive' && ($segments[1] ?? null) === 'category') {
                $fail('The prefix can\'t be "exclusive/category" — that path is already used elsewhere on the site.');
            }
        };
    }

    /** See Admin\StoreController::uniqueSlug() for the rationale. */
    private function uniqueSlug(string $title, int $regionId, ?int $exceptId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 1;

        while (
            Blog::where('region_id', $regionId)->where('slug', $slug)
                ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }

    private function syncRelatedBlogs(Request $request, Blog $blog): void
    {
        $relatedIds = collect($request->input('related_blog_ids', []))
            ->filter(fn ($id) => (int) $id !== $blog->id && Blog::where('id', $id)->where('region_id', $blog->region_id)->exists())
            ->values();

        $blog->relatedBlogs()->sync(
            $relatedIds->mapWithKeys(fn ($id, $index) => [$id => ['sort_order' => $index + 1]])->all()
        );
    }

    private function validated(Request $request, ?Blog $blog = null): array
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'alpha_dash',
                Rule::unique('blogs', 'slug')->where('region_id', $region->id)->ignore($blog),
            ],
            'route_prefix' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+(\/[a-z0-9-]+)*$/', $this->notReservedPrefix()],
            'route_suffix' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+(\/[a-z0-9-]+)*$/'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048', 'dimensions:width=670,height=300'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'schema_type' => ['required', 'in:Article,BlogPosting'],
            'faqs' => ['nullable', 'array'],
            'faqs.*.question' => ['nullable', 'string', 'max:500'],
            'faqs.*.answer' => ['nullable', 'string'],
        ]);

        $processed = BlogContentProcessor::extractToc($data['content']);
        $data['content'] = $processed['content'];
        $data['toc'] = $processed['toc'];
        $data['reading_time_minutes'] = BlogContentProcessor::estimateReadingTimeMinutes($processed['content']);

        $data['faqs'] = collect($data['faqs'] ?? [])
            ->filter(fn ($faq) => filled($faq['question'] ?? null) && filled($faq['answer'] ?? null))
            ->values()
            ->all();

        $data['is_published'] = $request->boolean('is_published');
        $data['robots_index'] = $request->boolean('robots_index');
        $data['robots_follow'] = $request->boolean('robots_follow');
        $data['auto_compress_images'] = $request->boolean('auto_compress_images');
        $data['convert_to_webp'] = $request->boolean('convert_to_webp');
        $data['enable_amp'] = $request->boolean('enable_amp');
        $data['auto_link_related_blogs'] = $request->boolean('auto_link_related_blogs');

        return $data;
    }
}
