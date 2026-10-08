<?php

namespace Pixelant\PxaProductManager\Domain\Repository;

/*
 *  Copyright notice
 *
 *  (c) 2014
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

use Pixelant\PxaProductManager\Domain\Model\DTO\DemandInterface;
use Pixelant\PxaProductManager\Domain\Model\Filter;
use Pixelant\PxaProductManager\Event\Repository\GetProductQueryBuilderEvent;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ProductRepository extends AbstractDemandRepository
{
    use CanFindByUidList;

    public const TABLE_NAME = 'tx_pxaproductmanager_domain_model_product';

    /**
     * Returns object class name.
     *
     * @return string
     */
    public function getObjectClassName(): string
    {
        return \Pixelant\PxaProductManager\Domain\Model\Product::class;
    }

    /**
     * Create Demand QueryBuilder.
     *
     * @param DemandInterface $demand
     * @return QueryBuilder
     */
    public function createDemandQueryBuilder(DemandInterface $demand): QueryBuilder
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_NAME);

        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer::class));

        $selectFields = [self::TABLE_NAME . '.*'];

        $selectFields = $this->addAdditionalSelectFields($selectFields);

        $queryBuilder
            ->select(...$selectFields)
            ->from(self::TABLE_NAME);

        $this->addStorageExpression($queryBuilder, $demand);

        $this->addProductPagesExpression($queryBuilder, $demand);

        $this->addFilters($queryBuilder, $demand);

        $this->addLimit($queryBuilder, $demand);

        $this->addOffset($queryBuilder, $demand);

        $this->addLanguageRestriction($queryBuilder);

        $this->addOrderings($queryBuilder, $demand);

        $this->demandService->getSortBy([
            'tx_pxaproductmanager_domain_model_product' => 'tx_pxaproductmanager_domain_model_product',
        ], $queryBuilder);

        $event = GeneralUtility::makeInstance(GetProductQueryBuilderEvent::class, $queryBuilder);
        $this->eventDispatcher->dispatch($event);

        return $event->getqueryBuilder();
    }

    /**
     * Add product pages to querybuilder expression if set.
     *
     * @param QueryBuilder $queryBuilder
     * @param DemandInterface $demand
     */
    protected function addProductPagesExpression(QueryBuilder $queryBuilder, DemandInterface $demand): void
    {
        $pageTreeStartingPoint = $demand->getPageTreeStartingPoint();
        if ($pageTreeStartingPoint) {
            $singleViewPages = GeneralUtility::makeInstance(PageRepository::class)
                ->getPageIdsRecursive([(int)$pageTreeStartingPoint], 5);

            if ($singleViewPages === []) {
                $queryBuilder->andWhere('1=0');
                return;
            }

            $queryBuilder->andWhere(
                $queryBuilder->expr()->in(
                    'uid',
                    $this->getProductPagesSubQuery($singleViewPages, $queryBuilder)
                )
            );
        }
    }

    /**
     * Returns product pages in subquery.
     *
     * @param array $singleViewPageIds
     * @param QueryBuilder $parentQueryBuilder
     * @return string
     */
    protected function getProductPagesSubQuery(array $singleViewPageIds, QueryBuilder $parentQueryBuilder): string
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');

        return $queryBuilder->select('tpppm.uid_local')
            ->from('pages')
            ->join(
                'pages',
                'tx_pxaproductmanager_product_pages_mm',
                'tpppm',
                $queryBuilder->expr()->eq(
                    'tpppm.uid_foreign',
                    $queryBuilder->quoteIdentifier('pages.uid')
                ) .
                ' AND tpppm.tablenames = \'pages\'' .
                ' AND tpppm.fieldname = \'doktype\''
            )
            ->where(
                $queryBuilder->expr()->in(
                    'pages.uid',
                    $parentQueryBuilder->createNamedParameter(
                        $singleViewPageIds,
                        \Doctrine\DBAL\ArrayParameterType::INTEGER
                    )
                )
            )
            ->getSQL();
    }

    /**
     * Get attribute filter subquery.
     *
     * @param int $attributeId
     * @param mixed $values
     * @param QueryBuilder $parentQueryBuilder
     * @param string $conjunction
     * @return string
     */
    protected function getAttributeSubQuery(
        int $attributeId,
        $values,
        QueryBuilder $parentQueryBuilder,
        string $conjunction
    ): string {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_pxaproductmanager_domain_model_attribute');

        $expressionBuilder = $queryBuilder->expr();

        if ($conjunction === 'and') {
            $function = 'and';
        } else {
            $function = 'or';
        }

        $conditions = $expressionBuilder->{$function}();

        foreach ($values as $value) {
            $conditions = $conditions->with(
                $expressionBuilder->or(
                    $expressionBuilder->eq(
                        'tpdmav.value',
                        $parentQueryBuilder->createNamedParameter($value, \Doctrine\DBAL\ParameterType::INTEGER)
                    ),
                    $expressionBuilder->inSet(
                        'tpdmav.value',
                        $expressionBuilder->literal((string)(int)$value)
                    )
                )
            );
        }

        $subQuery = $queryBuilder->select('tpdmav.product')
            ->from('tx_pxaproductmanager_domain_model_attribute', 'tpdma')
            ->join(
                'tpdma',
                'tx_pxaproductmanager_domain_model_attributevalue',
                'tpdmav',
                $queryBuilder->expr()->eq(
                    'tpdmav.attribute',
                    $queryBuilder->quoteIdentifier('tpdma.uid')
                )
            )
            ->where(
                $queryBuilder->expr()->eq(
                    'tpdma.uid',
                    $parentQueryBuilder->createNamedParameter($attributeId, \Doctrine\DBAL\ParameterType::INTEGER)
                ),
            )
            ->andWhere($conditions)
            ->groupBy('tpdmav.product');

        return $subQuery->getSQL();
    }

    /**
     * Get category filter subquery.
     *
     * @param int $categoryId
     * @param mixed $values
     * @param QueryBuilder $parentQueryBuilder
     * @param string $conjunction
     * @return string
     */
    protected function getCategoriesSubQuery(
        int $categoryId,
        $values,
        QueryBuilder $parentQueryBuilder,
        string $conjunction
    ): string {
        $values = array_values(array_unique(array_map('intval', (array)$values)));
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_category');
        $queryBuilder->select('scrm.uid_foreign')->from('sys_category', 'sc')
            ->join('sc', 'sys_category_record_mm', 'scrm', $queryBuilder->expr()->eq('scrm.uid_local', $queryBuilder->quoteIdentifier('sc.uid')))
            ->where(
                $queryBuilder->expr()->in('sc.uid', $parentQueryBuilder->createNamedParameter($values, \Doctrine\DBAL\ArrayParameterType::INTEGER)),
                $queryBuilder->expr()->eq(
                    'scrm.tablenames',
                    $parentQueryBuilder->createNamedParameter(self::TABLE_NAME, \Doctrine\DBAL\ParameterType::STRING)
                ),
                $queryBuilder->expr()->eq(
                    'scrm.fieldname',
                    $parentQueryBuilder->createNamedParameter('categories', \Doctrine\DBAL\ParameterType::STRING)
                )
            )->groupBy('scrm.uid_foreign');
        if ($conjunction === 'and' && $values !== []) {
            $queryBuilder->having('COUNT(DISTINCT scrm.uid_local) = ' . count($values));
        }
        return $queryBuilder->getSQL();
    }

    /**
     * Add filters to querybuilder expression if set.
     *
     * @param QueryBuilder $queryBuilder
     * @param DemandInterface $demand
     * @return void
     */
    protected function addFilters(QueryBuilder $queryBuilder, DemandInterface $demand): void
    {
        $filters = $demand->getFilters();
        if (empty($filters)) {
            return;
        }

        $conjunction = $demand->getFilterConjunction();

        if ($conjunction === 'and') {
            $function = 'and';
        } else {
            $function = 'or';
        }

        $expressionBuilder = $queryBuilder->expr();

        $conditions = $expressionBuilder->{$function}();

        foreach ($filters as $filterData) {
            if (!is_array($filterData) || !isset($filterData['type'], $filterData['value'])
                || !is_array($filterData['value'])) {
                $queryBuilder->andWhere('1=0');
                continue;
            }
            $type = (int)$filterData['type'];
            $conjunction = ($filterData['conjunction'] ?? 'and') === 'or' ? 'or' : 'and';
            $value = $filterData['value'];
            if ($value === []) {
                continue;
            }

            if ($type === Filter::TYPE_CATEGORIES) {
                $conditions = $conditions->with(
                    $queryBuilder->expr()->in(
                        'uid',
                        $this->getCategoriesSubQuery((int)($filterData['attribute'] ?? 0), $value, $queryBuilder, $conjunction)
                    )
                );
            } elseif ($type === Filter::TYPE_ATTRIBUTES) {
                $conditions = $conditions->with(
                    $queryBuilder->expr()->in(
                        'uid',
                        $this->getAttributeSubQuery((int)($filterData['attribute'] ?? 0), $value, $queryBuilder, $conjunction)
                    )
                );
            } else {
                $queryBuilder->andWhere('1=0');
            }
        }

        if (count($conditions) > 0) {
            $queryBuilder->andWhere($conditions);
        }
    }

    /**
     * Adds additional select fields defined in typoscript settings.
     *
     * @param array $selectFields
     * @return array
     */
    protected function addAdditionalSelectFields(array $selectFields): array
    {
        // Fetch listView settings.
        $configuration = (array)$this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'PxaProductManager'
        );
        $listViewSettings = (array)($configuration['listView'] ?? []);

        // If any additionalFields are set, we need to add them to selectFields.
        if (!empty($listViewSettings['additionalFields'])) {
            $additionalFields = $listViewSettings['additionalFields'] ?? [];
            if (!empty($additionalFields)) {
                $additionalFieldsList = GeneralUtility::trimExplode(',', $additionalFields, true);
                foreach ($additionalFieldsList as $additionalField) {
                    $selectFields[] = self::TABLE_NAME . '.' . $additionalField;
                }
            }
        }

        // If any additionalAttribute is set, we need to fetch attributes_values field.
        if (!empty($listViewSettings['additionalAttributes'])) {
            $selectFields[] = self::TABLE_NAME . '.attributes_values';
        }

        return $selectFields;
    }

    /**
     * Add restriction based on current site language.
     *
     * @param QueryBuilder $queryBuilder
     */
    protected function addLanguageRestriction(QueryBuilder $queryBuilder): void
    {
        $languageId = GeneralUtility::makeInstance(Context::class)->getAspect('language')->getId();

        $languageField = (string)($GLOBALS['TCA'][self::TABLE_NAME]['ctrl']['languageField'] ?? '');
        if ($languageField === '') {
            return;
        }

        $queryBuilder->andWhere(
            $queryBuilder->expr()->in($languageField, [$languageId, -1])
        );
    }
}
