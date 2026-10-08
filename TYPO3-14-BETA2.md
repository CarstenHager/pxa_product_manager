# Product Manager – TYPO3 14 Beta 2

Der Stand ist für TYPO3 14.3.x und PHP 8.2–8.4 vorbereitet. Vor dem Einsatz
bitte eine Staging-Kopie von Dateien und Datenbank erstellen.

Enthalten sind die v14-CType-Registrierung, der Upgrade-Wizard für bestehende
`list_type`-Inhaltselemente, Site Sets, v14-TCA/FormEngine-Anpassungen,
Doctrine-DBAL-4-Typen sowie die ViewFactory-/ViewHelper-Migration. Es werden
keine Produkt-, Kategorie-, Filter-, Attribut- oder FAL-Daten gelöscht. Die
dynamische Auswahl der Produkt-Manager-Modi wird über TYPO3s PSR-14-
FlexForm-Events aufgebaut; die entfernte `flexParsing`-Hook-API wird nicht
mehr verwendet.

Die statische QA umfasst PHP-Lint (225 Dateien im Gesamtpaket), das Parsing von
4 JSON-, 14 YAML- und 39 XML-Dateien, die Syntaxprüfung von 17 JavaScript- und
22 TypoScript-Dateien. Ein echter TYPO3-v14-Boot sowie ein
Backend-/Frontend-Test gegen eine Projektdatenbank ist in der Build-Umgebung
nicht verfügbar und muss auf der Staging-Installation erfolgen.

Solr ist in dieser Beta bewusst nicht geprüft und wird nicht automatisch als
Service geladen.

## Fluid-5-Hotfix vom 08.10.2026

- `LinkViewHelper`: den in Fluid 5 entfernten Aufruf
  `registerUniversalTagAttributes()` entfernt. HTML-Attribute werden vom
  `AbstractTagBasedViewHelper` automatisch übernommen.
- `RenderMultipleViewHelper`: `parent::__construct()` entfernt, da die
  Fluid-5-Basisklasse keinen Konstruktor hat.
- Standalone-Regression mit echten Fluid-5.3.2- und TYPO3-14.3.7-Core-/
  Extbase-Klassen: alle acht ViewHelpers initialisieren ihre Argumente.
  Sieben Link-Rendering-Szenarien bestanden (HTML-Inhalt, universelle
  Attribute/Escaping, Ziel/absolute URL, data-/aria-Attribute, URL-Ausgabe,
  additionalAttributes und aufeinanderfolgende Links). Beide Fehler sind
  mit dem vorherigen Beta-2-Code reproduziert worden.
- Die URL-Erzeugung war dabei ein Testdouble; eine vollständige TYPO3-Site
  samt Projektdatenbank wurde nicht gestartet.

## FlexForm-Hotfix vom 08.10.2026

- In `Configuration/TCA/Overrides/tt_content.php` bei allen vier
  Plugin-Registrierungen den fehlenden Präfix `FILE:` vor den
  `EXT:`-FlexForm-Dateipfaden ergänzt. Ohne diesen Präfix versucht TYPO3,
  den Pfad als XML zu parsen; die Formularvorbereitung überspringt die
  ungültige Struktur und `TcaFlexProcess` wirft anschließend #1480765571.
- Standalone-Regression mit den echten TYPO3-14.3.7-Klassen
  `ExtensionUtility`, `FlexFormTools`, `TcaMigration`, `TcaPreparation`,
  `TcaFlexPrepare` und `TcaFlexProcess`: Der bisherige Fehler ist für
  alle vier Plugin-Typen reproduziert. Mit der Korrektur sind die
  Datenstruktur-Kennung und alle vier XML-Formulare aufgelöst.
- Testwerte für die Produkt-Auswahl und das Limit bleiben in der
  Formularvorbereitung erhalten. Die Prüfung verwendet minimales
  `tt_content`-TCA, Roh-TCA über einen Test-Schema-Lookup und isolierte
  Paket-/Cache-Initialisierung; kein vollständiger Backend-/Site-Boot.
- Die bestehenden FlexForm-XML-Dateien lösen noch sechs unterschiedliche
  automatisch verarbeitete Deprecation-Meldungen aus (`eval=int` und
  numerische `items`-Schlüssel). Dieser Hotfix ändert ausschließlich die
  vier Dateiverweise und keine gespeicherten Daten oder XML-Feldnamen.
