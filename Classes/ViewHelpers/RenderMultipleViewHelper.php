<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class RenderMultipleViewHelper extends AbstractViewHelper
{
    protected ViewFactoryInterface $viewFactory;

    public function __construct(?ViewFactoryInterface $viewFactory = null)
    {
        $this->viewFactory = $viewFactory ?? GeneralUtility::makeInstance(ViewFactoryInterface::class);
    }

    /**
     * @var array
     */
    protected $extbaseConfiguration = [];

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Initialize arbuments.
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('key', 'string', 'Rendering stack key', false, null);
        $this->registerArgument('arguments', 'array', 'Arguments', false, null);
    }

    /**
     * Initialize viewhelper.
     *
     * @return void
     */
    public function initialize(): void
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        // Include ExtensionName and set PluginName to prevent missing extbaseConfiguration
        // when other plugins are rendered on the same page.
        $this->extbaseConfiguration = (array)$configurationManager->getConfiguration(
            ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK,
            'PxaProductManager',
            'RenderMultiple'
        );
    }

    /**
     * Render viewhelper.
     *
     * @return string
     */
    public function render(): string
    {
        $config = (array)($this->extbaseConfiguration['view']['renderingStacks'] ?? []);
        $key = (string)($this->arguments['key'] ?? '');
        $renderingStack = $config[$key] ?? [];

        if (!is_array($renderingStack) || $renderingStack === []) {
            return '';
        }

        $parts = 0;
        $output = '';
        $childContent = [];
        $arguments = $this->arguments['arguments'] ?? [];
        $this->arguments['arguments'] = is_array($arguments) ? $arguments : [];

        krsort($renderingStack, SORT_NUMERIC);

        foreach ($renderingStack as $renderingParts) {
            if (!is_array($renderingParts) || !is_string($renderingParts['template'] ?? null)
                || $renderingParts['template'] === '') {
                continue;
            }
            if ($parts === 0) {
                $this->arguments['arguments']['childContent'] = $this->renderChildren();
            } else {
                $this->arguments['arguments']['childContent'] = $childContent[$parts - 1] ?? '';
            }

            $childContent[$parts] = htmlspecialchars_decode(
                $this->getView($renderingParts['template'], $this->arguments)
            );
            $parts++;
        }
        if (is_array($childContent)) {
            $output = $childContent[count($childContent) - 1];
        }

        return $output;
    }

    /**
     * Create view from demanded template and arguments.
     *
     * @param string $path
     * @param array $arguments
     * @return string
     */
    public function getView(string $path, array $arguments): string
    {

        $view = $this->viewFactory->create(new ViewFactoryData(
            templateRootPaths: (array)($this->extbaseConfiguration['view']['templateRootPaths'] ?? []),
            partialRootPaths: (array)($this->extbaseConfiguration['view']['partialRootPaths'] ?? []),
            layoutRootPaths: (array)($this->extbaseConfiguration['view']['layoutRootPaths'] ?? []),
            format: 'html',
        ));
        $view->assignMultiple($arguments['arguments'] ?? []);

        return $view->render($path);
    }
}
