# Product Manager TYPO3 12 – Beta 5

## Behobener Fehler

Beim Bearbeiten eines Produkts brach die Ausgabe einer Attributzeile mit „Array to string conversion“ in BackendUtility::getRecordIconAltText ab. Der Nutzer meldete TYPO3 12.4.45; dieselbe problematische Verarbeitung ließ sich im vorhandenen Test-Core 12.4.37 nachvollziehen.

Das Attributfeld ist zugleich der TYPO3-Datensatztyp (ctrl.type). Als group-Feld lieferte FormEngine eine verschachtelte Relation, während der Icon-Tooltip einen einzelnen Typwert erwartet.

Korrektur:
- Attributrelation als selectSingle mit derselben foreign_table und unveränderten gespeicherten Attribut-IDs.
- Auswertung der IDs in Attributzeilen, Vererbungsinformationen und Formular-Wizards angepasst. Der gemeinsame Leser berücksichtigt rohe IDs, selectSingle-Daten sowie bisherige group-Daten.
- Neue Attributzeilen erhalten die passende einfache ID-Liste.
- Versteckte Formularfelder neuer Attributzeilen verarbeiten auch den aufbereiteten Sprachwert.

Keine Änderung am TYPO3-Core. Keine Änderung am SQL-Schema, keine Datenmigration und keine Löschung von Produkt- oder Attributwerten.

## Ausgeführte Prüfungen

TYPO3 12.4.37, PHP 8.3.6, SQLite, Warnungen/Notices als Fehler:
- 37 Backend-Prüfungen bestanden: einschließlich FormData-Kompilierung, des zuvor scheiternden Icon-Tooltip-Aufrufs und tatsächlichem FormEngine-Rendering für alle zehn Attributtypen; außerdem neue Attributzeile, verstecktes ID-Feld, Vererbung, Linkbrowser und Linkvorschau.
- Bestehende Regressionen erneut bestanden: 19 Repository/Demander-, 35 Konfigurations-, 3 Beta-2-, 4 Beta-3- und 10 HTTP-Prüfungen.
- CLI-Vererbung mit Testprodukten erneut bestanden.
- Alle 223 PHP-Dateien beider Extensions sowie JSON/XML/XLIFF/YAML und die zwei neuen Backend-JavaScript-Module syntaktisch geprüft.
- Archiv-CRC und byteweiser Vergleich zum Arbeitsstand geprüft.

Kein Testlauf unter 12.4.45 und kein Zugriff auf die Nutzerinstallation. Es bleiben TYPO3-12-Deprecations bestehen. Kein vollständiger interaktiver Backend-Klicktest und kein Test realer Datei-Uploads. Die Dateien/FAL-Attributformulare wurden mit leeren Dateirelationen gerendert.

## Installation

Product-Manager-Verzeichnis durch den vollständigen Inhalt dieser Beta-5-ZIP ersetzen, eigene Anpassungen vorher sichern. Demander Beta 2 bleibt unverändert. Anschließend ALLE TYPO3-Caches leeren, damit besonders die geänderte TCA-Konfiguration neu geladen wird; falls erforderlich Klasseninformationen neu aufbauen. Danach Produkt erneut öffnen.

Die bereits wiederhergestellten pi_flexform-Einstellungen bleiben erhalten. Für diese Korrektur keine SQL-Reparatur erneut ausführen.

## Noch offener Frontend-Fehler

Diese Beta behebt den vorliegenden Fehler beim Produktbearbeiten. Ob die separaten AJAX-HTTP-500-Fehler dieselbe oder eine andere Ursache haben, ist nicht nachgewiesen. Deren Serverantwort/Exception wird weiterhin für eine gezielte Diagnose benötigt.
