<?php
/**
 * @package BlackBrix Content Sliders
 * @license GPL-2.0-or-later
 */
namespace BlackBrix\Plugin\Content\Bbcontentsliders\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

final class Bbcontentsliders extends CMSPlugin implements SubscriberInterface
{
    private static int $nextGroup = 0;
    /** @var array<string, int> Used document-wide IDs; each occurrence gets a unique id. */
    private static array $usedIds = [];

    public static function getSubscribedEvents(): array
    {
        return ['onContentPrepare' => 'onContentPrepare'];
    }

    public function onContentPrepare(ContentPrepareEvent $event): void
    {
        if (!$this->getApplication()->isClient('site')) {
            return;
        }

        $item = $event->getItem();
        if (!is_object($item) || !isset($item->text) || !is_string($item->text)) {
            return;
        }

        if (!preg_match('~\{slide(?:r)?\s*=~i', $item->text)) {
            return;
        }

        // Do not produce interactive (initially hidden) markup in feeds or non-HTML output.
        $document = $this->getApplication()->getDocument();
        if (!$document instanceof HtmlDocument) {
            return;
        }

        $original = $item->text;
        $converted = $this->convert($original);
        if ($converted === $original) {
            return;
        }

        $item->text = $converted;
        $assets = $document->getWebAssetManager();
        $assets->registerAndUseStyle('plg_content_bbcontentsliders.styles', 'plg_content_bbcontentsliders/sliders.css');
        $assets->registerAndUseScript('plg_content_bbcontentsliders.script', 'plg_content_bbcontentsliders/sliders.js', [], ['defer' => true]);
    }

    private function convert(string $html): string
    {
        // Only complete slider blocks are changed. Other content remains byte-for-byte intact.
        // A standalone paragraph around the opening/closing shortcode is supported.
        $pattern = '~(?:<p\b[^>]*>\s*)?\{slide(?:r)?\s*=\s*([^}]+)\}(?:\s*</p>)?(.*?)(?:<p\b[^>]*>\s*)?\{/slide(?:r)?\}(?:\s*</p>)?~isu';

        return preg_replace_callback($pattern, function (array $match): string {
            $title = trim(strip_tags($match[1]));
            if ($title === '') {
                return $match[0];
            }

            $groupId = ++self::$nextGroup;
            $slug = $this->slug($title);
            $count = (self::$usedIds[$slug] ?? 0) + 1;
            self::$usedIds[$slug] = $count;
            $id = $count === 1 ? $slug : $slug . '-' . $count;
            $panelId = 'bbcs-panel-' . $groupId;
            $headingId = 'bbcs-heading-' . $groupId;
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeId = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');

            $options = [
                'exclusive' => (bool) $this->params->get('exclusive', 1),
                'openDuration' => $this->between($this->params->get('open_duration', 400), 0, 5000),
                'closeDuration' => $this->between($this->params->get('close_duration', 250), 0, 5000),
                'autoscroll' => (bool) $this->params->get('autoscroll', 1),
                'offset' => $this->between($this->params->get('scroll_offset', 60), 0, 1000),
                'scrollDuration' => $this->between($this->params->get('scroll_duration', 500), 0, 5000),
                'hashLinks' => (bool) $this->params->get('hash_links', 1),
            ];
            $settings = htmlspecialchars(json_encode($options, JSON_THROW_ON_ERROR), ENT_QUOTES, 'UTF-8');

            // Parameters live on each slider to support independently prepared article fragments.
            return '<section class="bbcs-item" id="' . $safeId . '" data-bbcs-settings="' . $settings . '">'
                . '<h3 class="bbcs-heading" id="' . $headingId . '">'
                . '<button class="bbcs-toggle" type="button" aria-expanded="false" aria-controls="' . $panelId . '">'
                . '<span class="bbcs-symbol" aria-hidden="true"></span>'
                . '<span class="bbcs-title">' . $safeTitle . '</span>'
                . '</button></h3>'
                . '<div class="bbcs-panel" id="' . $panelId . '" role="region" aria-labelledby="' . $headingId . '" hidden>'
                . '<div class="bbcs-inner">' . $match[2] . '</div></div></section>';
        }, $html) ?? $html;
    }

    private function slug(string $title): string
    {
        $text = strtr($title, ['Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss']);
        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }
        $text = trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $text), '-');
        return $text !== '' ? $text : 'slider';
    }

    private function between($value, int $minimum, int $maximum): int
    {
        return max($minimum, min($maximum, (int) $value));
    }
}
