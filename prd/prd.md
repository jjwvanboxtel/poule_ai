# Product Requirements Document (PRD)
## Voetbalpoule & Voorspelsysteem (WK / EK / Toernooien)

---

## 1. Doel
Een webapplicatie (PHP, shared hosting) voor het beheren van voetbalpoules (zoals WK en EK), waarin deelnemers volledige voorspellingen indienen, punten verdienen volgens configureerbare regels en publieke standen worden weergegeven. Sub-competities maken ranglijsten binnen groepen mogelijk.

---

## 2. Rollen & Rechten

### Rollen
- **Gast**
  - Geen account
  - Kan landingspagina, uitslagen en standen bekijken

- **Deelnemer**
  - Account met registratie
  - Doet voorspellingen voor competities
  - Kan alles wat een gast kan

- **Beheerder**
  - Configureert en beheert competities
  - Beheert gebruikers en beheerders
  - Kan alles wat deelnemer en gast kunnen

### Regels
- Rollenhiërarchie: beheerder > deelnemer > gast
- De **laatste beheerder mag nooit verwijderd, gedeactiveerd of gedegradeerd worden**

---

## 3. Registratie & Accounts

### Registratie deelnemer
Velden:
- Voornaam
- Achternaam
- E-mail (uniek)
- Telefoonnummer
- Wachtwoord (gehasht)

### Betaling
- Beheerder kan per deelnemer vastleggen of er betaald is (vinkje)
- Betalingsstatus kan gebruikt worden om deelname te blokkeren of te markeren

### Beheerdersbeheer
- Beheerders kunnen andere beheerders toevoegen, bewerken en verwijderen
- Zelfverwijdering en verwijderen van laatste beheerder is niet toegestaan

---

## 4. Competities

### Competitie-eigenschappen
- Naam
- Beschrijving
- Startdatum
- Einddatum
- Uiterste inleverdatum
- Actief / inactief
- Inlegbedrag
- Prijsverdeling (1e, 2e, 3e plaats in percentages, totaal = 100%)
- Logo afbeelding

### Landingspagina (publiek)
Toont per competitie:
- Naam en logo
- Beschrijving
- Start- en einddatum
- Aantal deelnemers (betaald)
- Totale inleg
- Prijzengeld (1e, 2e, 3e plaats)

---

## 5. Secties (Configureerbaar per Competitie)

Een competitie bestaat uit secties die aan/uit gezet kunnen worden:
- Groepsfase uitslagen
- Winnaar / verliezer / gelijkspel
- Gele en rode kaarten
- Knock-out fase
- Bonusvragen

Per sectie is het **aantal punten configureerbaar**.

---

## 6. Wedstrijden, Groepen & Speelsteden

### Groepen
- Competities zoals WK/EK hebben groepen (A, B, C, …)
- Wedstrijden kunnen aan een groep gekoppeld zijn

### Speelsteden
- Wedstrijden zijn gekoppeld aan een speelstad (stadion / stad)
- Speelstad wordt weergegeven bij wedstrijdinformatie

---

## 7. Bonusvragen & Entiteiten

### Vraagtypes
- Entity-gebaseerd (landen, teams, spelers, scheidsrechters)
- Numeriek (aantallen)
- Open tekst

### Entiteiten
- Landen
- Teams
- Spelers
- Nederlandse spelers (filter op nationaliteit)
- Scheidsrechters

### Validatie & UI
- Entity-vragen gebruiken dropdowns
- Antwoorden worden gevalideerd tegen actieve entiteiten

### CSV-import
- Spelers (en andere entiteiten) kunnen in bulk worden toegevoegd via CSV

---

## 8. Voorspellingen & Inleveren

### Kernregel
- Een deelnemer vult **altijd alle actieve secties volledig in**
- Daarna wordt de voorspelling **in één keer definitief ingeleverd**
- Geen gedeeltelijke inzendingen toegestaan

### Deadline
- Na de uiterste inleverdatum:
  - Geen inzendingen meer
  - Voorspellingen zijn read-only

---

## 9. Puntentelling

- Volledig **data-gedreven**
- Configureerbaar per competitie en per sectie
- Geen hardcoded puntregels

Voorbeelden:
- Exacte uitslag
- Juiste uitslag
- Juiste winnaar / gelijkspel
- Kaarten (geel / rood)
- Knock-out winnaar
- Bonusvragen

---

## 10. Sub-Competities

### Functionaliteit
- Sub-competities horen bij een hoofdcompetitie
- Bevatten een **subset van deelnemers**
- Hebben een eigen standenlijst
- Gebruiken dezelfde voorspellingen en punten als de hoofdcompetitie

Voorbeelden:
- Vriendengroep
- Kantoor
- Familie

---

## 11. Standenlijsten

### Weergave per deelnemer
- Huidige positie
- Naam
- Totaal aantal punten
- Stijging / daling / gelijk
- Aantal plaatsen verschil

### Techniek
- Standen worden opgeslagen als snapshots
- Vergelijking met vorige snapshot bepaalt positie-verandering

### Indicatoren
- Gestegen
- Gedaald
- Gelijk
- Nieuw

Werkt voor:
- Hoofdcompetities
- Sub-competities
- Publieke (gast) weergave

---

## 12. Technische Eisen

- PHP 8.x
- MySQL
- Shared hosting (LAMP)
- Server-side rendering
- PDO + prepared statements
- CSRF-beveiliging
- Server-side rol- en rechtenchecks

---

## 13. Niet-functionele Eisen

- Responsive UI (bijv. Bootstrap 5)
- Goede performance
- Uitbreidbaar (nieuwe secties / regels)
- Eén codebase voor alle toernooien

---

---

## 16. Testen

- Gebruik Test driven development
- Schrijf unit testen
- Gebruik Playwright om te valideren of dat de applicatie werkt en dat de UI/UX er goed uit ziet

## 15. Succescriteria

- Competities volledig configureerbaar
- Deadlines strikt enforced
- Transparante puntentelling en standen
- Publiek inzicht zonder account
- Onderhoudbare en schaalbare architectuur

---

**Einde PRD**
