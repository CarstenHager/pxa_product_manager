<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Service;

use Pixelant\PxaProductManager\Domain\Model\Category;
use Pixelant\PxaProductManager\Domain\Model\DTO\CategoryDemand;
use Pixelant\PxaProductManager\Domain\Model\DTO\NavigationItem;

/**
 * Builds the category navigation used by the legacy category-list controller.
 *
 * The product list controller has its own tree handling, but the category
 * controller is still part of the public extension API and may be configured
 * by integrators. Keeping the service available also prevents an autoload
 * failure when that controller is used.
 */
final class NavigationService
{
    private bool $expandAll = false;

    public function __construct(
        private readonly ?Category $rootCategory = null,
        private readonly ?CategoryDemand $demand = null,
    ) {
    }

    public function setExpandAll(bool $expandAll): void
    {
        $this->expandAll = $expandAll;
    }

    /**
     * @return NavigationItem[]
     */
    public function getItems(): array
    {
        if ($this->rootCategory === null || $this->rootCategory->isHideSubCategories()) {
            return [];
        }

        $currentUid = $this->getCurrentCategoryUid();
        return $this->buildItems($this->rootCategory->getSubCategories(), $currentUid);
    }

    /**
     * @param iterable<Category> $categories
     * @return NavigationItem[]
     */
    private function buildItems(iterable $categories, int $currentUid): array
    {
        $items = [];
        foreach ($categories as $category) {
            if (!$category instanceof Category || $category->isHiddenInNavigation()) {
                continue;
            }
            if (
                $this->demand?->isHideCategoriesWithoutProducts()
                && $category->getProducts()->count() === 0
                && $category->getSubCategories()->count() === 0
            ) {
                continue;
            }

            $subItems = [];
            if (!$category->isHideSubCategories() && ($this->expandAll || $this->containsCurrentCategory($category, $currentUid))) {
                $subItems = $this->buildItems($category->getSubCategories(), $currentUid);
            }

            $isCurrent = $category->getUid() === $currentUid;
            $items[] = (new NavigationItem(
                $category,
                $isCurrent || $subItems !== [],
                $isCurrent
            ))->setSubItems($subItems);
        }

        return $items;
    }

    private function containsCurrentCategory(Category $category, int $currentUid): bool
    {
        if ($currentUid > 0 && $category->getUid() === $currentUid) {
            return true;
        }
        foreach ($category->getSubCategories() as $child) {
            if ($child instanceof Category && $this->containsCurrentCategory($child, $currentUid)) {
                return true;
            }
        }

        return false;
    }

    private function getCurrentCategoryUid(): int
    {
        $parent = $this->demand?->getParent();
        return $parent instanceof Category ? $parent->getUid() : (int)$parent;
    }
}
