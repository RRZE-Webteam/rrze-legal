[![Aktuelle Version](https://img.shields.io/github/package-json/v/rrze-webteam/rrze-legal/main?label=Version)](https://github.com/RRZE-Webteam/rrze-legal) [![Release Version](https://img.shields.io/github/v/release/rrze-webteam/rrze-legal?label=Release+Version)](https://github.com/rrze-webteam/rrze-legal/releases/) [![GitHub License](https://img.shields.io/github/license/rrze-webteam/rrze-legal)](https://github.com/RRZE-Webteam/rrze-legal) [![GitHub issues](https://img.shields.io/github/issues/RRZE-Webteam/rrze-legal)](https://github.com/RRZE-Webteam/rrze-legal/issues)

# RRZE Legal 

Generator für rechtliche Pflichtangaben auf einem Webauftritt.


## Zweck 

Der Generator erstellt die im deutschen und europäischen Rechtsraum verbindlichen Seiten für ein Impressum, einer Datenschutzerklärung und einer Barrierefreiheitserklärung.

Die vorkonfigurierten und optionalen Inhaltstexte und Rechtsnormen beziehen sich hierbei in der aktuellen Version auf den Rahmen, den Einrichtungen des öffentlichen Dienstes ein Bayern 
unterworfen sind.
Voreingestellte Werte sind vorhanden für
- die Friedrich-Alexander-Universität Erlangen-Nürnberg (FAU) 
- das Universitätsklinikum Erlangen (UK)
- die Technische Universität Nürnberg (UTN)


## Keine Gewährleistung für Rechtstexte

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

Administratoren von Websites können im Backend unter "Rechtliche Pflichtangaben" individuelle Anpasungen an den Texten vornehmen.
Ausserdem können dort die jeweiligen Pflichtdaten (z.B. Angaben zur verantwortlichen Person) ergänzt werden.


## Anpassung für andere Hochschulen und Einrichtungen

Das Plugin enthält rechtliche Texte, Organisationsdaten und Voreinstellungen für die Friedrich-Alexander-Universität Erlangen-Nürnberg (FAU), das Universitätsklinikum Erlangen (UK) und die Technische Universität Nürnberg (UTN). Andere Hochschulen oder Einrichtungen können das Plugin als Grundlage verwenden, müssen die Inhalte jedoch fachlich, organisatorisch und rechtlich auf ihre eigene Situation anpassen.

Erstellen und pflegen Sie dafür einen eigenen Fork des Projekts. Änderungen sollten nicht direkt in einer produktiven Installation vorgenommen werden, damit sie nachvollziehbar bleiben und bei späteren Updates übernommen werden können.

Die wichtigsten Anpassungspunkte sind:

- `data/tos.php`: Organisationsdaten, Bezeichnungen, Kontaktangaben, vertretungsberechtigte Personen sowie Aufsichtsbehörden. Neue Organisationen werden über den Array `items` ergänzt.
- `templates/tos/`: Deutsche und englische Textbausteine für Impressum, Datenschutzerklärung und Barrierefreiheitserklärung. Die Haupttemplates binden die einzelnen Abschnitte und optionalen Bestandteile ein.
- `data/consent-categories.php`: Fest definierte Kategorien für Einwilligungen und Cookies.
- `data/consent-cookies.php`: Voreinstellungen für Cookies, externe Dienste, Datenschutzinformationen, technische Angaben und mögliche Plugin-Abhängigkeiten.
- `data/useragents.php`: Vorgaben zur Erkennung bekannter Bots, Crawler oder institutionseigener Clients.
- `settings/`: Definition der im WordPress-Backend und in den Netzwerkeinstellungen verfügbaren Eingabefelder und Optionen.
- `languages/`: Übersetzungen für alle im PHP-Code verwendeten Texte. Neue oder geänderte Ausgabetexte müssen mindestens für Deutsch und Englisch gepflegt werden.

Die Standardeinstellungen können in einer Multisite-Installation zentral durch Superadmins gesteuert werden. Site-Administratoren ergänzen anschließend die Daten ihrer jeweiligen Website, soweit dies durch die Netzwerkeinstellungen erlaubt ist.

Nach Änderungen an JavaScript, SCSS, Metadaten oder der Readme müssen die Build-Skripte ausgeführt werden:

```sh
npm install
npm run dev
```

Für eine produktive Auslieferung wird stattdessen `npm run prod` verwendet. Dadurch werden die Assets, Plugin-Metadaten und die WordPress-Readme aktualisiert.

Die rechtliche Prüfung und Freigabe der angepassten Texte bleibt Verantwortung der jeweiligen Einrichtung. Das Plugin stellt technische und redaktionelle Bausteine bereit, ersetzt aber keine Rechtsberatung.

