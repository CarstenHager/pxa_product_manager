# TYPO3 13 Beta 2

Diese Beta baut auf der funktionierenden TYPO3-12-Beta5 und der ersten V13-Beta auf.

Zusätzlich zu Beta 1 wurden folgende V13-Stellen korrigiert:

- `BackendWorkspaceRestriction` durch `WorkspaceRestriction` ersetzt
- `ContentObjectRenderer->getTreeList()` durch `PageRepository`-Methoden ersetzt
- TSFE-Zugriffe auf aktuelle Seiten-ID und Rootline über TYPO3-13-Request-Attribute ergänzt
- Sprach-Overlay bei Produktdaten auf `PageRepository::getLanguageOverlay()` umgestellt
- native Datei-TCA-Konfiguration um `foreign_types`, `allowed` und `disallowed` ergänzt
- Abhängigkeit von `pxa_product_manager` auf `demander` 0.4 aktualisiert
- unbenutzte Legacy-Tree-Klasse entfernt

Die Beta wurde gegen die vorhandene TYPO3-12-QA-Installation auf Regressionen geprüft. Ein vollständiger Lauf mit einem TYPO3-13-Core sowie reale Backend-/Datei-Upload-Tests stehen noch aus.

Solr bleibt außerhalb des Prüfumfangs.
