<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Utility;

/**
 * Builds a file relation TCA configuration for TYPO3 13.
 *
 * TYPO3 13 removed ExtensionManagementUtility::getFileFieldTCAConfig().
 * Keeping the small compatibility helper in the extension allows the
 * existing field definitions to retain their relation options while using
 * the native TCA type "file".
 */
final class TcaFileFieldConfiguration
{
    /**
     * @param string $fieldName Kept for API compatibility with the former core helper.
     * @param array<string, mixed> $additionalConfiguration
     * @param string $allowedFileExtensions Comma-separated file extensions.
     * @param string $disallowedFileExtensions Comma-separated disallowed extensions.
     * @return array<string, mixed>
     */
    public static function get(
        string $fieldName,
        array $additionalConfiguration = [],
        string $allowedFileExtensions = '',
        string $disallowedFileExtensions = ''
    ): array {
        $configuration = [
            'type' => 'file',
        ];

        if ($allowedFileExtensions !== '') {
            $configuration['allowed'] = $allowedFileExtensions;
        }
        if ($disallowedFileExtensions !== '') {
            $configuration['disallowed'] = $disallowedFileExtensions;
        }

        // The former helper accepted foreign_types for the inline
        // sys_file_reference record. The native file type uses the same
        // structure below overrideChildTca.types.
        if (isset($additionalConfiguration['foreign_types'])) {
            $additionalConfiguration['overrideChildTca']['types'] = array_replace_recursive(
                $additionalConfiguration['overrideChildTca']['types'] ?? [],
                $additionalConfiguration['foreign_types']
            );
            unset($additionalConfiguration['foreign_types']);
        }

        // The native file type determines these values from the field itself.
        unset($additionalConfiguration['foreign_match_fields']);

        return array_replace_recursive($configuration, $additionalConfiguration);
    }
}
