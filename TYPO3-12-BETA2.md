# Product Manager Beta 2 – 08.10.2026

Demander Beta 1 bleibt unverändert kompatibel.

## Korrekturen

- CustomProductController: Fehlende Einstellungen `customProductsList` / `mode` verwenden den bisherigen Demand-Fallback, ohne PHP-Warnung. Eine fehlende explizite Produktliste wird als leere Liste behandelt.
- Die gemeinsam verwendete Relationstabelle `tx_pxaproductmanager_attributeset_record_mm` erhält einen expliziten Primärschlüssel über `(uid_local, uid_foreign, tablenames, fieldname)`. Ein automatisch erzeugter Schlüssel nur über die beiden IDs würde unterschiedliche Beziehungstypen unzulässig zusammenfassen.

## Update

Product-Manager-Verzeichnis durch diese Version ersetzen, System-/PHP-Caches leeren und die Datenbankanalyse erneut starten. Demander muss nicht ersetzt werden. Composer-Paketversion: `12.0.1-beta2`, Extension-Manager-Version weiterhin `12.0.1`, Status Beta.

Vor dem Hinzufügen des korrigierten Primärschlüssels diese reine Leseabfrage ausführen:

```sql
SELECT uid_local, uid_foreign, tablenames, fieldname, COUNT(*) AS anzahl
FROM tx_pxaproductmanager_attributeset_record_mm
GROUP BY uid_local, uid_foreign, tablenames, fieldname
HAVING COUNT(*) > 1;
```

Bei Treffern den Primärschlüssel noch nicht hinzufügen und die Duplikate fachlich prüfen. Keine automatische Löschung von Beziehungen. Falls bereits ein Primärschlüssel über nur zwei Spalten angelegt wurde, zunächst `SHOW CREATE TABLE tx_pxaproductmanager_attributeset_record_mm;` prüfen und die Änderung gesondert planen.

Die für diese Korrektur relevanten Datenbankänderungen betreffen ausschließlich die genannte Relationstabelle. Die im Projekt vorgeschlagenen Umbenennungen anderer Tabellen/Felder sind keine notwendige Maßnahme für diesen Fehler. Entfernungen vorerst abwählen, Upgrade-Wizards und Abnahme der gesamten Installation separat abschließen.

## Prüfung

Fehlende Custom-List-Konfiguration und fehlender Modus ohne Warnungen getestet; PHP-Warnungen wurden im Test zu Exceptions. Der von TYPO3 12.4.37 aus SQL und TCA erzeugte Schemaschlüssel wurde auf alle vier Spalten geprüft. PHP-Syntaxprüfung erfolgreich. Es wurden keine Daten der Anwenderinstallation geändert. Die ursprünglichen Beta-1-Testgrenzen gelten weiterhin.
