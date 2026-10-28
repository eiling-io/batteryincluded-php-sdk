---
lang: de
permalink: /backend-configuration.html
title: Backend-Konfiguration
nav_order: 5
---

# Backend-Konfiguration
{: .no_toc }

1. TOC
{:toc}

Das SDK sendet Dokumente und liest Ergebnisse, aber **wie** gesucht wird, konfigurierst du im
[BatteryIncluded Portal](https://portal.batteryincluded.io): welche Felder filterbar sind, welche Felder die Vorschläge
speisen und wie relevant jedes Feld für die Suche ist.

| Bereich | Menü | Was du konfigurierst | Wirkung im SDK |
|---|---|---|---|
| [Schema](#schema) | Integration → Schema | Feld ist filterbar, Sprache, Bezeichnung, Einheit | Feld kann ein Filter werden, `FacetDto::getFieldLabel()`, `getFieldUnit()` |
| [Filter](#filters) | Configuration → Filters | Angezeigte Filter im Ergebnis und ihr Typ | `BrowseResponse::getFacets()` |
| [Suggest](#suggest) | Configuration → Suggest | Felder für Vervollständigungen und vorgeschlagene Dokumente | `SuggestResponse` |
| [Relevanz](#relevance) | Configuration → Relevance | Durchsuchte Felder, ihre Priorität und Tippfehlertoleranz | Treffer und ihre Reihenfolge in `BrowseResponse::getHits()` |

{: .wichtig }
Konfiguriere das Portal nach dem ersten Sync. Es kann nur Felder anbieten, die in deinen Dokumenten schon existieren.
Jede Seite hat einen eigenen **Save**-Button, Änderungen greifen erst nach dem Speichern.

## Schema {#schema}

**Integration → Schema** zeigt die Datenstruktur deiner Dokumente. Ein Feld muss als **Filterable** markiert sein,
bevor du es als Filter konfigurieren kannst.

![Schema der Collection]({{ '/assets/images/backend/01-schema.png' | relative_url }})

1. Öffne das Feld im Baum **Data structure**, zum Beispiel `type`.
2. In der **Field configuration** rechts:
   - trage die **Bezeichnung** ein, die dein Shop anzeigt, zum Beispiel „Dokumenttyp“, und bei numerischen Feldern die
     **Unit** (zum Beispiel `€`),
   - wähle die **Sprache** des Felds,
   - aktiviere **Filterable**.
3. Klicke auf **Save schema**.

Die Zähler über dem Baum (**Filterable**, **Sortable**) zeigen, wie viele Felder aktiviert sind.

### Übersetzte Felder {#translated-fields}

Mit [Übersetzungen](translations) hat jede Locale eigene Felder unterhalb von `_PRODUCT_i18n`, zum Beispiel
`_PRODUCT_i18n › en › properties › Storage`. Konfiguriere jede Locale einzeln: Markiere das Feld als filterbar, setze die
Sprache der Locale (hier **English**) und trage die Bezeichnung in dieser Sprache ein.

![Filterbares Feld einer Locale]({{ '/assets/images/backend/02-schema-filterable.png' | relative_url }})

Die Bezeichnung wird mit jeder Facette als `FacetDto::getFieldLabel()` ausgeliefert, dein Shop muss Filternamen also
nicht übersetzen. Halte Eigenschaftsnamen in allen Locales gleich (zum Beispiel immer `Color`), nur die Bezeichnungen
unterscheiden sich.

{: .hinweis }
Der Tab **Variables** des Schemas enthält die Variablen der Collection, zum Beispiel die Variable `locale` aus den
[Übersetzungen](translations#collection-setup).

## Filter {#filters}

**Configuration → Filters** legt fest, welche filterbaren Felder als Filter erscheinen und wie.

![Filterkonfiguration]({{ '/assets/images/backend/03-filters.png' | relative_url }})

1. Wähle ein Feld unter **Add field configuration**. Übersetzte Felder enthalten die Variable
   `_PRODUCT_i18n › locale › …`, so bedient ein Filter jede Sprache: Das Gateway ersetzt `locale` durch die Locale der
   Anfrage.
2. Wähle den Filtertyp:

   | Typ | Wofür | SDK-Klasse |
   |---|---|---|
   | Select (Default) | Eigenschaften, zum Beispiel Farbe oder Speicher | `FacetSelectDto` |
   | Select (Tree) | Kategoriepfade wie `Electronics > TVs` | `FacetCategoryDto` |
   | Range | Zahlen, zum Beispiel der Preis | `FacetRangeDto` |
   | Rating | Sternebewertungen | `FacetRatingDto` |
   | Ignore / System | Felder, die nicht als Filter erscheinen sollen | – |

   ![Filtertypen]({{ '/assets/images/backend/04-filter-edit.png' | relative_url }})

3. Passe bei Bedarf die weiteren Optionen an, zum Beispiel **Match any (OR)** dafür, wie mehrere ausgewählte Werte
   kombiniert werden.
4. Sortiere die Filter mit dem Griff links. `getFacets()` liefert sie in dieser Reihenfolge.
5. Klicke auf **Save**.

Übergib `getFacets()` das Feld des Kategoriebaums, damit das SDK den Baum aufbaut:

```php
$searchStruct = new BrowseSearchStruct();
$searchStruct->setLocale('en');

$result = (new BrowseService($apiClient))->browse($searchStruct);

foreach ($result->getFacets('_PRODUCT_i18n.categories') as $facet) {
    echo $facet->getFieldName() . ' => ' . $facet->getFieldLabel() . PHP_EOL;
}
```

Der [Demo-Shop](demo-shop) zeigt alle konfigurierten Filter in seiner Seitenleiste.

## Suggest {#suggest}

**Configuration → Suggest** steuert die [Vorschläge](suggestions) während der Eingabe. Mit **Preview suggest** oben
probierst du deine Änderungen aus.

![Suggest-Konfiguration]({{ '/assets/images/backend/05-suggest.png' | relative_url }})

| Abschnitt | Inhalt | SDK |
|---|---|---|
| Generate word completion based on | Felder, aus denen die Vervollständigungen entstehen, zum Beispiel `ordernumber`, `ean` und der übersetzte `name` | `SuggestResponse::getQueryCompletions()` |
| Product suggestions | Zusätzliche Felder, die mit jedem vorgeschlagenen Dokument zurückkommen | `SuggestResponse::getDocuments()` |
| First Click Suggestions | Vorschläge, bevor der Besucher tippt, optional pro Variable wie `locale` | – |

Nur die Felder unter **Product suggestions** sind Teil der vorgeschlagenen Dokumente. Füge dort `id` hinzu, wenn dein
Shop Vorschläge mit Detailseiten verlinkt (die Dokument-ID im Root, zum Beispiel `PRODUCT-1002`), und die Felder für
Bild und URL, wenn deine Vorschlagsliste sie anzeigt.

## Relevanz {#relevance}

**Configuration → Relevance → Search** legt fest, welche Felder durchsucht werden und wie wichtig ein Treffer in jedem
Feld ist.

![Relevanzkonfiguration]({{ '/assets/images/backend/06-relevance.png' | relative_url }})

1. Füge mit **Add search field** ein Feld hinzu, zum Beispiel `_PRODUCT_i18n › locale › name`.
2. Stelle die **Priority** mit dem Schieberegler ein. Ein Treffer in einem Feld mit höherer Priorität rankt das Dokument
   höher.
3. Setze **Typos**, die Anzahl tolerierter Tippfehler für dieses Feld. Verwende `0` für exakte Werte wie `ean` oder
   `ordernumber`.
4. Klicke auf **Save**.

Nur die hier aufgeführten Felder durchsucht `BrowseSearchStruct::setQuery()`. Ohne Suchbegriff (Kategorieseiten) oder
mit einer [Sortierung](search#sorting) hat die Priorität keinen Einfluss auf die Reihenfolge. Die Tabs **Ranking** und
**Variants** enthalten weitere Ranking-Optionen.

## Fehlersuche {#troubleshooting}

| Symptom | Ursache |
|---|---|
| `getFacets()` liefert ein leeres Array | Unter Filters sind keine Filter konfiguriert, oder die Collection wurde neu angelegt und die Konfiguration ist weg |
| Ein Feld fehlt unter **Add field configuration** | Das Feld ist im Schema nicht **Filterable**, oder es existiert noch nicht (erst synchronisieren) |
| Ein Filter zeigt den Feldnamen statt einer Bezeichnung | Im Schema ist für dieses Feld und diese Locale keine Bezeichnung gepflegt |
| Ein Filter wirkt nicht | Der Feldname weicht vom konfigurierten Filter ab (zum Beispiel `_PRODUCT.` statt `_PRODUCT_i18n.`) |
| Filter anderer Sprachen erscheinen (`_PRODUCT_i18n.de.properties.Color`) | Erwartet, das Gateway liefert sie mit. Blende Felder mit einem Locale-Segment in deiner Oberfläche aus |
| Der Kategoriefilter ist eine flache Liste | Verwende **Select (Tree)** und übergib das Feld an `getFacets()` |
| Ein Suchbegriff findet nichts | Das Feld mit dem Begriff steht nicht unter Relevance → Search |
| Vorschlägen fehlt ein Feld, zum Beispiel `id` oder das Bild | Das Feld steht nicht unter Product suggestions |
| Suggest-Anfragen scheitern mit 500 | Übersetzte Filter sind konfiguriert, aber die Anfrage hat keine Locale, siehe [Übersetzungen](translations#requesting-a-locale) |
