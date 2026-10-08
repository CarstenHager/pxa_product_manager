# TYPO3 13 Beta 1

Diese Beta basiert auf der zuletzt funktionierenden TYPO3-12-Beta5.

Enthaltene erste V13-Anpassungen:

- TYPO3-13- und PHP-8.2-bis-8.4-Abhängigkeiten in `composer.json` und `ext_emconf.php`
- Ersatz von `ConfigurationManagerInterface->getContentObject()` durch das aktuelle Request-Attribut
- Ersatz des in TYPO3 13 entfernten `ExtensionManagementUtility::getFileFieldTCAConfig()` durch eine native TCA-File-Konfiguration
- explizite Registrierung von `ext_typoscript_setup.txt` über `addTypoScriptSetup()`
- bestehende Filterkorrektur aus Beta5 bleibt enthalten

Diese Beta wurde vor der vollständigen TYPO3-13-Prüfung paketiert. Die ausführliche Prüfung von Extension Scanner, TCA, Backend, Frontend, AJAX, FlexForms und Datenbestand folgt nach der Übergabe.

Solr bleibt außerhalb des Prüfumfangs.
