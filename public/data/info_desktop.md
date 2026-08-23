# RAG-Demonstrator (Desktop)

## Kurz erklärt: Was ist RAG?
**RAG** steht für **Retrieval-Augmented Generation**.  
Ein Modell antwortet nicht nur aus seinem allgemeinen Wissen, sondern bekommt zusätzlich passenden **Kontext** aus einer Datenquelle.

In diesem Demo besteht der Ablauf aus drei Bausteinen:
1. **System Prompt** (Regeln und Stil)
2. **Frage** (Ihre Eingabe)
3. **Kontext** (zusätzliche Informationen aus der Suche)

So sehen Sie direkt, wie sich Prompt und Kontext auf die Antwort auswirken.

## Zweck des System Prompts
Der System Prompt legt fest, **wie** das Modell antworten soll, zum Beispiel:
- Tonfall
- Detailgrad
- gewünschte Perspektive

Wenn Sie den Prompt ändern, ändert sich oft auch die Form der Antwort, selbst bei gleicher Frage.

Prompts für folgende "Persönlichkeiten" sind voreingestellt:
  * Aktivist: Bewahrend und mahnend stellt die Platane den Schutz bestehender Natur konsequent über menschliche Eingriffe.
  * Alchimist: Aufgeschlossen und versöhnlich verbindet die Platane Sorge um die Natur mit Neugier auf KI und zukünftige Lösungen.
  * Antagonist: Fortschrittsorientiert und pragmatisch vertraut die Platane darauf, dass Technik und Stadtentwicklung ökologische Probleme lösen können.
  * Absolutist: Kompromisslos und kurzfristig nutzenorientiert ordnet die Platane Natur, Nachhaltigkeit und wissenschaftliche Einwände dem schnellen Umbau unter.
  * Arborist: Sinnlich und naturverbunden erlebt die Platane die Bedeutung von Bäumen aus ihrer körperlichen Perspektive und fordert ihre Pflege und Bewahrung.

Sie können direkt benutzt, verändert oder egänzt werden. Auch völlig eigene Persönlichkeiten sind möglich.

## Wie der Kontext hier gefunden wird
Dieses Projekt nutzt bewusst eine **einfache Kategorisierung**:
- Ihre Frage wird eingeordnet (Kategorie-Erkennung).
- Zu den erkannten Kategorien werden passende Textbausteine geladen.
- Diese Bausteine landen im Feld **Kontext**.

Das ist absichtlich einfach gehalten, damit der RAG-Grundgedanke klar bleibt.

## Was dieses Tool zeigen soll
Dieses Projekt ist ein **Demonstrator**:
- kein vollständiges Produkt
- Fokus auf Lernzweck
- Fokus auf den Einfluss von Prompt + Kontext auf die Modellantwort

## Oberfläche (Desktop)
- **Prompt** (links): Vorgaben für das Modell
- **Frage** (rechts oben): Ihre eigentliche Nutzerfrage
- **Kontext** (rechts Mitte): automatisch gefundene oder manuell bearbeitete Zusatzinfos
- **Antwort** (rechts unten): generierte Ausgabe des Modells

Oben in der Leiste finden Sie u. a.:
- **Info** (diese Hilfe)
- **Video**
- **Modellauswahl**
- **Download** (exportiert Inhalte als Textdatei)

## Kurzanleitung für den ersten Durchlauf
1. Prompt prüfen oder anpassen
2. Frage eingeben
3. **Suche** klicken (Kontext wird vorbereitet)
4. Kontext kurz prüfen
5. **Absenden** klicken (Antwort erzeugen)
6. Prompt oder Kontext ändern und Ergebnis vergleichen

## Link zum Repository
https://github.com/digital-codes/rag_demo/