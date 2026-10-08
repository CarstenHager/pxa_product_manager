<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AttributeIdentifierController.
 */
class AttributeIdentifierController
{
    /**
     * Convert string for attribute identifier field.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function attributeIdentifierConvertAction(ServerRequestInterface $request): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        $queryParameters = is_array($parsedBody) ? $parsedBody : [];
        $value = $queryParameters['value'] ?? '';
        if (!is_scalar($value)) {
            return new JsonResponse(['success' => false, 'output' => ''], 400);
        }
        $output = trim((string)$value);

        if (!empty($output)) {
            $output = GeneralUtility::underscoredToLowerCamelCase(
                GeneralUtility::makeInstance(CharsetConverter::class)->specCharsToASCII('utf-8', $output)
            );
        }

        return new JsonResponse(['success' => true, 'output' => $output]);
    }
}
