# Product Manager Beta 3 – 08.10.2026

Enthält die Korrekturen aus Beta 2. Demander bleibt unverändert.

RenderMultipleViewHelper prüft fehlende view/renderingStacks-Konfiguration und fehlende Stack-Schlüssel ohne PHP-Warnung. Das bisherige Verhalten bleibt bestehen: Ohne konfigurierten Stack wird nichts ausgegeben. Zusätzlich wird die interne Sammlung als Array statt false initialisiert und fehlende Argumente werden als leeres Array behandelt. Optionale Template-Pfadlisten erhalten leere Defaults.

Die fehlende Konfiguration selbst wird nicht ersetzt. Bitte im aktiven TypoScript-Setup `plugin.tx_pxaproductmanager.view.renderingStacks` prüfen. Das statische Product-Manager-TypoScript aus `EXT:pxa_product_manager/Configuration/TypoScript/` muss eingebunden sein und darf anschließend nicht versehentlich überschrieben/gelöscht werden. Bei eigener Konfiguration sind eigene Stack-Definitionen möglich.

Geprüft: Fehlende View-Konfiguration, fehlende renderingStacks, unbekannter Stack-Schlüssel und richtige Verschachtelung eines konfigurierten Stacks mit fehlenden Argumenten. PHP-Warnungen/Deprecations werden im Regressionstest als Fehler behandelt. PHP-Syntaxprüfung erfolgreich.

Update: Product-Manager-Dateien ersetzen und TYPO3-/PHP-Caches leeren. Keine zusätzlichen Datenbankänderungen durch Beta 3. Der Beta-2-Hinweis zum vierteiligen MM-Primärschlüssel gilt weiterhin. Keine Produktdaten werden durch diesen Fix gelöscht oder umgeschrieben.
