[![Aktuelle Version](https://img.shields.io/github/package-json/v/rrze-webteam/rrze-legal/main?label=Version)](https://github.com/RRZE-Webteam/rrze-legal) [![Release Version](https://img.shields.io/github/v/release/rrze-webteam/rrze-legal?label=Release+Version)](https://github.com/rrze-webteam/rrze-legal/releases/) [![GitHub License](https://img.shields.io/github/license/rrze-webteam/rrze-legal)](https://github.com/RRZE-Webteam/rrze-legal) [![GitHub issues](https://img.shields.io/github/issues/RRZE-Webteam/rrze-legal)](https://github.com/RRZE-Webteam/rrze-legal/issues)
![GitHub milestone details](https://img.shields.io/github/milestones/progress-percent/RRZE-Webteam/RRZE-legal/3)

# RRZE Legal 

Generator für rechtliche Pflichtangaben auf einem Webauftritt.

## Version

Version: 2.8.9


## Zweck 

Der Generator erstellt die im deutschen und europäischen Rechtsraum verbindlichen Seiten für ein Impressum, einer Datenschutzerklärung und einer Barrierefreiheitserklärung.

Die vorkonfigurierten und optionalen Inhaltstexte und Rechtsnormen beziehen sich hierbei in der aktuellen Version auf den Rahmen, den Einrichtungen der Friedrich-Alexander-Universität Erlangen-Nürnberg (FAU) unterworfen sind.


## Keine Gewährleistung

Es kann und wird keine Gewährleistung und Garantie gegeben auf die rechtliche Korrektheit 
und Aktualität der von diesem Plugin erzeugten Rechtstexte.
Dies gilt insbesondere für Teile der Datenschutzerklärung.


## Endpoints

Dieses WordPress-Plugin erstellt die drei Endpoint-Seiten
- /impressum
- /datenschutz 
- /barrierefreiheit
 
bzw. auf Websites mit englischer Sprache die Endpoints
-  /imprint
-  /privacy
-  /accessibility


## Individuelle Anpassungen

Administratoren von Websites können im Backend unter "Einstellungen" &gt; "Rechtliche Pflichtangaben" individuelle Anpasungen an den Texten vornehmen.
Ausserdem können dort die jeweiligen Pflichtdaten (z.B. Angaben zur verantwortlichen Person) ergänzt werden.


## Anpassungen an andere Universitäten und Einrichtungen für universitäre WordPress-Betreiber

Das Plugin ist gedacht zum Einsatz in einer WordPress-Multisite-Instanz. 
Administratoren von einzelnen WordPress-Instanzen sollen bei solchen Angeboten die vorgegebenen Texte und Optionen, die in den "Einstellungen" vorgegeben werden, üblicherweise nicht ändern können.

(Super)Administratoren von Multisite-Instanzen können daher Anpassungen an die lokalen Gegebenheiten nur durch die direkte Änderung der Template-Dateien im Order ```templates/content/``` vornehmen.
Dort finden sich alle Templates für die einzelnen, optional einschaltbaren Absätze und Endpoint-Seiten.

Die Liste der in den "Einstellungen" konfigurierbaren Settings wird durch den $settings-Array in der Datei ```settings/settings.php```definiert. 
Die Auswahlliste zu den Aufsichtsbehörden aus Deutschland, deren Daten derzeit noch nicht vollständig sind, findet sich in selber Datei im Array $rechtsraum .

Wir empfehlen für die eigenen Anpassungen der Rechtstexte und der Optionen ein eigenen Branch des GitHub-Projektes zu machen.
