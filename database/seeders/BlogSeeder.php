<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Region;
use App\Support\BlogContentProcessor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Genuinely written shopping/savings articles (not Lorem Ipsum) so
     * every seeded post reads like real editorial content. `category`
     * matches a BlogCategorySeeder::$names entry.
     */
    public static array $posts = [
        [
            'title' => '10 Smart Ways to Save Money While Shopping Online',
            'category' => 'Shopping Tips',
            'excerpt' => 'From timing your purchases to stacking codes the right way, these ten habits can meaningfully cut your online shopping bill.',
            'sections' => [
                [
                    'title' => 'Why Small Habits Add Up',
                    'content' => "<p>Online shopping makes it easy to compare prices, but it also makes it easy to overpay without realizing it. A few consistent habits — checking for an active promo code before checking out, comparing prices across a couple of retailers, and waiting for a known sale window — can shave a meaningful amount off your total spend over a year.</p><p>None of these habits require much extra time. Most take less than a minute per purchase, and the savings compound the more often you shop online.</p>",
                ],
                [
                    'title' => 'Ten Habits Worth Building',
                    'content' => "<ol><li>Always search for a promo code before entering payment details.</li><li>Sign up for a retailer's newsletter — first-order discounts are common.</li><li>Add items to your cart and wait a day; many sites send a reminder discount.</li><li>Compare shipping costs, not just item prices, across retailers.</li><li>Check if the retailer has a loyalty or rewards program.</li><li>Use a bookmark for verified coupon pages instead of random search results.</li><li>Buy seasonal items just after the season peaks, when discounts are deepest.</li><li>Read the terms on a coupon — minimum spend and exclusions matter.</li><li>Track price history where possible so a \"sale\" is an actual discount.</li><li>Set a personal budget before browsing, not after adding items to cart.</li></ol>",
                ],
            ],
            'faqs' => [
                ['question' => 'Do I really save that much by checking for coupon codes?', 'answer' => "Even a 10-15% discount adds up quickly if you shop online regularly — it's one of the easiest habits to build since it costs nothing but a few seconds before checkout."],
                ['question' => "Is it worth signing up for every retailer's newsletter?", 'answer' => 'Only for stores you actually shop at repeatedly — otherwise it\'s easier to check a coupon site right before you buy.'],
            ],
        ],
        [
            'title' => 'How to Stack Coupons for Maximum Savings',
            'category' => 'Deals News',
            'excerpt' => 'Combining a promo code with cashback and a loyalty discount can multiply your savings — if you know the right order to apply them.',
            'sections' => [
                [
                    'title' => "What \"Stacking\" Actually Means",
                    'content' => "<p>Stacking simply means combining more than one discount on the same order — for example, a percentage-off promo code, a site-wide cashback rate, and a loyalty program discount, all on one purchase. Not every retailer allows every combination, but many allow at least two of the three.</p><p>The key is understanding which discounts are applied at checkout (like a promo code) versus which are tracked separately after purchase (like cashback), since they don't compete with each other.</p>",
                ],
                [
                    'title' => 'A Simple Stacking Order',
                    'content' => '<p>A reliable order to try: first apply any loyalty or membership discount if the retailer offers one, then apply the best available promo code at checkout, and finally make sure cashback tracking is active before you click "buy" (most cashback tools work through a browser extension or a special link you click before shopping).</p><p>If a retailer\'s checkout only allows one code field, prioritize the promo code with the highest percentage or dollar value, and let cashback run in the background.</p>',
                ],
            ],
            'faqs' => [
                ['question' => 'Can I use two promo codes at once?', 'answer' => 'Usually not — most retailers only accept one code per order in the checkout field. Stacking typically means combining a code with cashback or a loyalty discount instead.'],
                ['question' => "Why didn't my cashback track?", 'answer' => 'This usually happens when a coupon code redirects you away from the retailer\'s site first, or when an ad blocker interferes with tracking — try clicking through directly and disabling extensions during checkout.'],
            ],
        ],
        [
            'title' => 'Your Guide to Black Friday and Cyber Monday Deals',
            'category' => 'Seasonal Sales',
            'excerpt' => 'The biggest shopping weekend of the year rewards a bit of planning — here\'s how to get ready before the deals go live.',
            'sections' => [
                [
                    'title' => 'Before the Weekend Starts',
                    'content' => '<p>The best Black Friday and Cyber Monday shoppers do most of their work in advance. Make a list of what you actually want to buy, note the regular price at a couple of retailers, and set price alerts if the store supports them. This makes it much easier to tell a genuine discount from an inflated "was" price.</p><p>It also helps to create accounts and save payment details ahead of time at any retailer you plan to buy from — checkout speed matters when a popular item\'s stock is limited.</p>',
                ],
                [
                    'title' => 'Getting the Most Out of the Weekend',
                    'content' => "<p>Black Friday deals tend to focus on electronics and big-ticket items, while Cyber Monday often brings stronger discounts on clothing, home goods, and online-only retailers. If an item you want doesn't go on sale on Friday, it's often worth waiting to check again on Monday.</p><p>Keep an eye on return policies too — some retailers shorten or restrict returns on holiday sale items, so it's worth reading the fine print before you buy.</p>",
                ],
            ],
            'faqs' => [
                ['question' => 'Is Cyber Monday actually cheaper than Black Friday?', 'answer' => 'It depends on the category — electronics deals tend to be strongest on Black Friday, while Cyber Monday often has better discounts on clothing and online-exclusive retailers.'],
                ['question' => 'Should I wait until the last minute to buy?', 'answer' => "Only for items with plenty of stock. For anything in high demand, it's safer to buy as soon as you see a price you're happy with."],
            ],
        ],
        [
            'title' => 'Cashback vs. Coupons: Which Saves You More?',
            'category' => 'Shopping Tips',
            'excerpt' => "Both lower your final cost, but they work differently — here's how to decide which one to reach for on your next purchase.",
            'sections' => [
                [
                    'title' => 'How Each One Works',
                    'content' => '<p>A coupon or promo code reduces your total at checkout, so the discount is visible and immediate. Cashback works differently — you pay full price, then receive a percentage of your spend back later, usually after a tracking and confirmation period.</p><p>Because of that difference, coupons are better when you want an instant, guaranteed discount, while cashback rewards patience and tends to work best for purchases you\'d make anyway.</p>',
                ],
                [
                    'title' => 'Choosing Between Them',
                    'content' => '<p>If a retailer only offers one or the other, the choice is simple. When both are available, compare the numbers: a 20% promo code usually beats 5% cashback in the moment, but stacking a smaller code with cashback often beats using just one.</p><p>For big purchases you were going to make regardless of a sale, cashback is close to "free money" — just make sure you understand the tracking window and payout minimum before counting on it.</p>',
                ],
            ],
            'faqs' => [
                ['question' => 'Can I lose my cashback after earning it?', 'answer' => "Some programs reverse cashback if you return the item or if the retailer disputes the tracked sale, so it's worth reading the program's terms."],
                ['question' => 'Which is better for a small order?', 'answer' => 'A coupon code, since most cashback programs have a minimum payout threshold that a single small order might not clear on its own.'],
            ],
        ],
        [
            'title' => 'How to Spot a Fake or Expired Promo Code',
            'category' => 'Deals News',
            'excerpt' => 'Not every code you find online still works — a few quick checks can save you a frustrating checkout experience.',
            'sections' => [
                [
                    'title' => 'Common Warning Signs',
                    'content' => '<p>A code that seems too good to be true — like an unusually large percentage off a popular brand — is worth a second look. Codes shared without any source, posted with no expiry date, or repeated verbatim across dozens of unrelated sites are more likely to be outdated or fabricated.</p><p>Genuine, currently active codes are usually attached to a specific offer, a visible expiry date, and sometimes a minimum spend or category restriction.</p>',
                ],
                [
                    'title' => 'How We Verify Codes',
                    'content' => "<p>Every code listed on this site is checked by our editorial team before publishing, and we mark each one with when it was last confirmed to work. If a code stops working, our team removes or updates the listing rather than leaving it live indefinitely.</p><p>As a shopper, it's still worth having a backup code in mind — if your first choice doesn't apply at checkout, try a second verified option before giving up on the discount altogether.</p>",
                ],
            ],
            'faqs' => [
                ['question' => "What should I do if a code doesn't work at checkout?", 'answer' => 'Double-check for extra spaces or expired formatting, confirm your cart meets any minimum spend, and try a second verified code if one is available.'],
                ['question' => 'How often are codes on this site checked?', 'answer' => 'Our editors regularly re-check listed codes and update or remove any that are no longer accepted by the retailer.'],
            ],
        ],
        [
            'title' => 'Building a Budget-Friendly Wardrobe with Discount Codes',
            'category' => 'Shopping Tips',
            'excerpt' => "A useful wardrobe doesn't require full-price shopping — here's how to build one gradually using sales and stacked discounts.",
            'sections' => [
                [
                    'title' => 'Start With a Short List',
                    'content' => '<p>Rather than buying everything at once, list the handful of pieces you actually need — a couple of versatile tops, one solid pair of shoes, an outer layer for the season. Shopping with a specific list makes it much easier to recognize a genuinely good deal when it shows up, instead of buying whatever happens to be discounted that week.</p><p>Once you have your list, it\'s worth checking two or three retailers rather than committing to the first one you land on — prices and available codes vary more than most people expect.</p>',
                ],
                [
                    'title' => 'Timing Your Purchases',
                    'content' => "<p>Clothing discounts follow a fairly predictable seasonal rhythm: end-of-season sales are usually the deepest, since retailers are clearing space for the next season's stock. Combining an end-of-season sale with an additional promo code often produces the best price of the year on a given item.</p><p>It's also worth checking for student, loyalty, or first-order discounts, which frequently stack on top of an already-discounted sale price.</p>",
                ],
            ],
            'faqs' => [
                ['question' => 'Is it better to buy off-season?', 'answer' => 'Yes — clothing retailers typically discount most heavily right after a season peaks, so buying winter items in late winter or spring often gets the best price.'],
                ['question' => "What if I can't find a code for the exact item I want?", 'answer' => "Check for a site-wide or category-wide code instead — most retailers run general promotions even when there isn't an item-specific one available."],
            ],
        ],
        [
            'title' => 'A Seasonal Sales Calendar: When to Buy What',
            'category' => 'Seasonal Sales',
            'excerpt' => 'Retail discounts follow a fairly predictable yearly pattern — here\'s a rough guide to when different categories go on sale.',
            'sections' => [
                [
                    'title' => 'The Yearly Pattern',
                    'content' => '<p>Most retail categories follow a similar rhythm: prices dip right after a season ends, ahead of major holidays, and during a few dedicated shopping events throughout the year. Electronics tend to see the deepest discounts around major end-of-year shopping events, while furniture and home goods are often discounted in January as retailers clear winter inventory.</p><p>Clothing follows the season closely — outerwear drops in price in late winter, and swimwear is typically cheapest in late summer, just before demand falls off.</p>',
                ],
                [
                    'title' => 'A Rough Month-by-Month Guide',
                    'content' => '<p>January: furniture, home goods, and fitness equipment. March–April: spring clothing and outdoor gear. July: summer clothing clearance and mid-year electronics sales. November: the year\'s broadest discounts across nearly every category. December: gift categories and last-minute travel deals.</p><p>This is only a general guide — always compare a current price against its recent history where possible, rather than assuming a "sale" label always means a genuine discount.</p>',
                ],
            ],
            'faqs' => [
                ['question' => 'Is there ever a bad time to buy something on sale?', 'answer' => 'Not exactly a bad time, but buying right before a bigger seasonal event for that category means you might miss a deeper discount just a few weeks later.'],
                ['question' => 'Do these patterns apply to every retailer?', 'answer' => "They're a general guide — individual retailers run their own promotions on their own schedule, so it's still worth checking for codes year-round."],
            ],
        ],
        [
            'title' => 'First-Time Online Shopper? Start With These Habits',
            'category' => 'Shopping Tips',
            'excerpt' => 'A few simple habits early on can save you money and hassle for every online purchase that follows.',
            'sections' => [
                [
                    'title' => 'Before You Buy Anything',
                    'content' => "<p>If you're new to online shopping, the most useful habit to build early is checking for a promo code before you pay — it takes a few seconds and there's no downside to checking, even if you don't find one every time. It's also worth reading a retailer's return policy before buying anything you're not fully sure about, since return windows and conditions vary a lot between stores.</p><p>Creating an account with a retailer you plan to use again is usually worthwhile too — it often unlocks order tracking, faster checkout, and occasional loyalty discounts.</p>",
                ],
                [
                    'title' => 'Staying Safe and Saving More',
                    'content' => "<p>Stick to retailers with clear contact information, visible reviews, and a real return policy — these are simple signs of a legitimate store. Beyond that, using a dedicated coupon site (rather than a random search) makes it much easier to find codes that are actually current, since listings are checked rather than scraped indiscriminately.</p><p>Finally, keep a simple record of what you've bought and for how much — it makes it much easier to recognize a genuinely good deal the next time you shop for something similar.</p>",
                ],
            ],
            'faqs' => [
                ['question' => "Is it safe to save my card details on a retailer's site?", 'answer' => "Reputable retailers use secure payment processing, but if you're ever unsure, using a one-time or virtual card number is a reasonable extra precaution."],
                ['question' => "What's the single best habit to start with?", 'answer' => "Checking for a valid promo code before checkout — it's fast, free, and works on almost every purchase."],
            ],
        ],
    ];

    public function run(): void
    {
        Region::all()->each(function (Region $region) {
            $blogCategories = BlogCategory::where('region_id', $region->id)->get()->keyBy('name');
            $blogs = collect();

            foreach (self::$posts as $index => $post) {
                $processed = BlogContentProcessor::processSections($post['sections']);

                $blog = Blog::create([
                    'region_id' => $region->id,
                    'blog_category_id' => $blogCategories->get($post['category'])?->id,
                    'sort_order' => $index + 1,
                    'title' => $post['title'],
                    'slug' => Str::slug($post['title']),
                    'excerpt' => $post['excerpt'],
                    'content_sections' => $processed['sections'],
                    'toc' => $processed['toc'],
                    'author_name' => 'Editorial Team',
                    'published_at' => now()->subDays((count(self::$posts) - $index) * 4),
                    'reading_time_minutes' => BlogContentProcessor::estimateReadingTimeMinutes(
                        collect($processed['sections'])->pluck('content')->implode('')
                    ),
                    'faqs' => $post['faqs'],
                    'is_published' => true,
                    'meta_title' => $post['title'],
                    'meta_description' => $post['excerpt'],
                    'og_title' => $post['title'],
                    'robots_index' => true,
                    'robots_follow' => true,
                    'schema_type' => 'BlogPosting',
                    'auto_compress_images' => true,
                    'convert_to_webp' => true,
                    'enable_amp' => false,
                    'auto_link_related_blogs' => true,
                ]);

                $blogs->push($blog);
            }

            // Demonstrate the manual picker on one post per region by
            // turning its auto-linking off and hand-picking 2 others.
            $manual = $blogs->first();
            $manual->update(['auto_link_related_blogs' => false]);
            $others = $blogs->where('id', '!=', $manual->id)->take(2)->values();
            foreach ($others as $index => $other) {
                $manual->relatedBlogs()->attach($other->id, ['sort_order' => $index + 1]);
            }
        });
    }
}
