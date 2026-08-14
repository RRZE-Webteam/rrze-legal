<?php

namespace RRZE\Legal\Consent\Categories;

defined('ABSPATH') || exit;

use RRZE\Legal\ListSettings;
use RRZE\Legal\Locale;

class Options extends ListSettings
{
    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->optionName = 'rrze_legal_consent_categories';
        $this->settingsFilename = 'consent-categories';
    }

    /**
     * Load consent categories from static data only.
     */
    public function loaded(): void
    {
        $this->options = $this->getStaticItems();
        $this->deleteStoredOptions();
    }

    /**
     * Consent categories are static and must not add a backend menu.
     */
    public function setAdminMenu()
    {
    }

    public function getItems(): array
    {
        if (empty($this->options)) {
            $this->options = $this->getStaticItems();
        }
        return $this->options;
    }

    public function getItemsCount(): int
    {
        return count($this->getItems());
    }

    public function getItemsOptions(): array
    {
        $options = [];
        foreach ($this->getItems() as $value) {
            if (!empty($value['id']) && !empty($value['name'])) {
                $options[$value['id']] = $value['name'];
            }
        }
        return $options;
    }

    public function getItemName($id): string
    {
        $items = $this->getItems();
        return $items[$id]['name'] ?? '';
    }

    public function getAllCategoriesNames($enabled = true): array
    {
        $options = [];
        foreach ($this->getItems() as $value) {
            $status = !empty($value['status']);
            if ($enabled && $status && !empty($value['id']) && !empty($value['name'])) {
                $options[$value['id']] = $value['name'];
            }
        }
        return $options;
    }

    protected function getStaticItems(): array
    {
        $data = $this->getStaticData();
        $items = $data['items'] ?? [];
        if (!is_array($items)) {
            return [];
        }

        foreach ($items as $key => $item) {
            if (!is_array($item)) {
                unset($items[$key]);
                continue;
            }
            $items[$key]['id'] = !empty($item['id']) ? (string) $item['id'] : (string) $key;
            $items[$key]['status'] = !empty($item['status']);
            $items[$key]['preselected'] = !empty($item['preselected']);
            $items[$key]['static'] = true;
            $items[$key]['position'] = isset($item['position']) ? absint($item['position']) : 99;
        }

        uasort($items, [$this, 'sortItemsByPosition']);
        return $items;
    }

    protected function sortItemsByPosition(array $a, array $b): int
    {
        return ($a['position'] ?? 99) <=> ($b['position'] ?? 99);
    }

    protected function deleteStoredOptions(): void
    {
        $langCodes = array_unique(['de', 'en', Locale::getLangCode()]);
        foreach ($langCodes as $langCode) {
            delete_option($this->optionName . '_' . $langCode);
            delete_option($this->optionName . '_' . $langCode . '_version');
        }
    }
}
