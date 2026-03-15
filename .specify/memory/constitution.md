# Project Constitution — Voetbalpoule & Voorspelsysteem

## Missie
Bouw een onderhoudbare, uitbreidbare webapplicatie voor het beheren van voetbalpoules (WK/EK/toernooien) die deelnemers complete voorspellingen laat indienen, punten berekent volgens configureerbare regels, en publieke standen toont.

## Belangrijke principes
- Scheid ‘wat/waarom’ (specificatie) van ‘hoe’ (plan/implementatie). Gebruik Spec‑Kit fases strikt.
- Agent‑gedreven automatisering en sjablonen definiëren artefactvormen; wijzig templates, niet agents, om outputstructuren aan te passen.
- Voorkom aannames over hosting of tooling: specificeer constraints expliciet in de planfase.

## Technische randvoorwaarden (non‑negotiables)
- PHP 8.x op gedeelde hosting (LAMP) is primaire runtime; server‑side rendering vereist.
- Gebruik PDO met prepared statements voor databanktoegang.
- CSRF‑bescherming en server‑side autorisatie/rolchecks verplicht.
- Deadlines moeten strikt gehandhaafd worden: na uiterste inleverdatum zijn voorspellingen read‑only.
- De laatste beheerder mag nooit verwijderd, gedeactiveerd of gedegradeerd worden.

## Acceptatiecriteria en kwaliteitsnormen
- Functioneel: volledige secties moeten verplicht ingevuld en als één transactie ingeleverd worden; geen gedeeltelijke inzendingen.
- Beveiliging: invoervalidatie, prepared statements, CSRF, en server‑side rolchecks; gevoelige data nooit in plain text.
- Testbare code: volg TDD-praktijken; unit tests voor core‑logica en Playwright voor end‑to-end UI‑validatie (E2E).
- Performance: standen worden als snapshots opgeslagen; renders en queries moeten schaalbaar zijn voor toernooien met veel deelnemers.
- Toegankelijkheid & responsiviteit: UI werkt op mobiel en desktop (bijv. Bootstrap 5 of vergelijkbaar).

## Ontwerpprincipes en operationele regels
- Puntentelling is volledig data‑gedreven en configureerbaar per competitie/sectie; geen hardcoded regels in de codebase.
- Sub‑competities delen voorspellingen en puntregels met de hoofdcompetitie; hun standen zijn afgeleid snapshots.
- Standen berekeningen en snapshotting moeten deterministisch en reproduceerbaar zijn.
- CSV‑import voor entiteiten (spelers, teams) ondersteund; import validatie tegen actieve entiteiten.

## Workflow / Governance
- Gebruik Spec‑Kit agents voor rollen: constitution → specify → plan → tasks → implement; wijzigingen aan principes vereisen expliciete constitutie‑updates.
- Scripts en agents moeten JSON‑vriendelijke outputs bieden (voorkeur voor `-Json` flags) om machineconsumptie te vergemakkelijken.
- Voordat `speckit.taskstoissues` issues mag openen, moet `git remote.origin.url` overeenkomen met de doel‑GitHub repo.

## Operationale richtlijnen voor ontwikkelaars
- Noem belangrijke beperkingen expliciet in de Specify/Plan documenten (bijv. shared hosting, PHP versie, PDO gebruik).
- Schrijf tests voor puntencalculatie en deadline‑enforcement; vertrouw niet alleen op handmatige QA.

---

Dit document is bedoeld als invoer voor downstream agents (.specify/templates, speckit.plan, speckit.tasks). Update alleen via de Spec‑Kit workflow (speckit.constitution → speckit.specify → speckit.plan) zodat de fasen gesynchroniseerd blijven.
