# Feature Specification: Voetbalpoule & Voorspelsysteem

**Feature Branch**: `001-voetbal-poule`  
**Created**: 2026-03-15  
**Status**: Draft  
**Input**: User description: "Voetbalpoule en voorspelsysteem voor WK EK en andere toernooien"

## Clarifications

### Session 2026-03-15

- Q: Hoe moet het systeem omgaan met deelnemers die nog niet betaald hebben? → A: Onbetaalde deelnemers mogen volledig deelnemen; het systeem markeert alleen zichtbaar dat ze nog niet betaald hebben.
- Q: Wanneer moet het systeem een nieuwe standensnapshot vastleggen? → A: Automatisch + handmatige herberekening.
- Q: Wanneer moeten sectie-instellingen en puntregels voor een competitie worden vergrendeld? → A: Nooit; beheerders mogen dit altijd wijzigen.
- Q: Wat moet er gebeuren als een competitie geen actieve secties heeft? → A: De competitie mag niet actief/open voor inzending zijn zolang er geen actieve secties zijn.
- Q: Hoe moet CSV-import omgaan met ongeldige of dubbele rijen? → A: De volledige import faalt zodra één rij ongeldig of dubbel is, met duidelijke foutmelding.

### Session 2026-03-16

- Q: Hoe moeten competitiestatussen exact worden afgebakend? → A: `draft` = niet publiek en geen inzendingen; `active` = publiek zichtbaar; `open` = inzendingen toegestaan tot de deadline; `closed` = publiek zichtbaar maar alleen-lezen na deadline; `archived` = historisch en alleen-lezen.
- Q: Hoe wordt een gebruiker deelnemer van een competitie? → A: Beide zijn toegestaan: zelfinschrijving voor open competities en handmatige koppeling door een beheerder.
- Q: Welke acties moet de beschermde onderhoudsworkflow zonder CLI/SSH minimaal ondersteunen? → A: Database-migraties uitvoeren en handmatige standherberekening starten.
- Q: Wat is de verplichte scope voor groepen, speelsteden en wedstrijden? → A: Alle drie moeten via de beheer-UI beheerbaar zijn; import is een optionele extra.
- Q: Hoe zichtbaar moet de markering voor onbetaalde deelnemers precies zijn? → A: Toon een duidelijke badge/label `Onbetaald` alleen aan beheerders en aan de betreffende deelnemer zelf in private views. Onbetaalde deelnemers verschijnen niet in publieke standenlijsten of andere publieke deelnemersoverzichten totdat zij als betaald zijn gemarkeerd.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Deelnemer levert volledige voorspelling in (Priority: P1)

Als deelnemer wil ik voor een competitie alle actieve voorspellingssecties volledig kunnen invullen en mijn voorspelling in een keer definitief kunnen indienen, zodat mijn deelname geldig is en correct kan worden beoordeeld.

**Why this priority**: Zonder volledige en definitieve voorspellingen bestaat de kern van de poule niet. Dit is het minimale waardevolle product voor deelnemers en organisatoren.

**Independent Test**: Kan zelfstandig worden getest door een competitie met actieve secties te openen, alle vereiste voorspellingen in te vullen, een definitieve inzending te doen en daarna te verifiëren dat de voorspelling read-only beschikbaar blijft.

**Acceptance Scenarios**:

1. **Given** een actieve competitie met open inzendtermijn en meerdere actieve secties, **When** een deelnemer alle verplichte velden invult en definitief indient, **Then** slaat het systeem de volledige voorspelling op als een enkele definitieve inzending.
2. **Given** een actieve competitie met open inzendtermijn, **When** een deelnemer probeert in te dienen terwijl een actieve sectie onvolledig is, **Then** weigert het systeem de inzending en toont welke onderdelen nog ontbreken.
3. **Given** een definitief ingediende voorspelling, **When** de deelnemer deze na inzending opent, **Then** toont het systeem de voorspelling als read-only.
4. **Given** een competitie waarvan de uiterste inleverdatum is verstreken, **When** een deelnemer een nieuwe of aangepaste voorspelling wil opslaan of indienen, **Then** weigert het systeem de actie.
5. **Given** een deelnemer met openstaande betaling, **When** deze een volledige voorspelling definitief indient binnen de deadline, **Then** accepteert het systeem de inzending en markeert het de betalingsstatus zichtbaar als onbetaald.
6. **Given** een competitie met een actieve knock-out sectie, **When** een deelnemer zijn voorspelling invult, **Then** kan deze per knock-out ronde de vereiste landen/teams selecteren volgens het voor die ronde geconfigureerde aantal posities.
7. **Given** een competitie met actieve bonusvragen, **When** een deelnemer een entity-gebaseerde bonusvraag invult, **Then** toont het systeem een dropdown met geldige actieve entiteiten en slaat het antwoord op als onderdeel van dezelfde definitieve inzending.
8. **Given** een onbetaalde deelnemer die is ingelogd, **When** deze zijn eigen competitie- of inzendpagina bekijkt, **Then** ziet alleen die deelnemer zelf een duidelijke `Onbetaald`-markering zonder dat inzendingen worden geblokkeerd.

---

### User Story 2 - Beheerder beheert competities, deelnemers en puntregels (Priority: P2)

Als beheerder wil ik competities, secties, deelnemers, betalingen en puntinstellingen kunnen beheren, zodat ik verschillende toernooien en poules kan opzetten zonder codewijzigingen.

**Why this priority**: De applicatie moet data-gedreven en herbruikbaar zijn voor meerdere toernooien. Zonder beheerfunctionaliteit blijft de oplossing statisch en niet inzetbaar in de praktijk.

**Independent Test**: Kan zelfstandig worden getest door als beheerder een competitie aan te maken of te wijzigen, secties aan of uit te zetten, punten per sectie te configureren, een deelnemer als betaald te markeren en de bewaakte beheerregels te valideren.

**Acceptance Scenarios**:

1. **Given** een beheerder met toegang tot competitiebeheer, **When** deze een competitie aanmaakt of bewerkt, **Then** kan hij naam, beschrijving, data, inleg, prijsverdeling, actiefstatus en logo vastleggen.
2. **Given** een competitie, **When** een beheerder secties activeert of deactiveert en puntenwaarden per sectie instelt, **Then** gebruikt het systeem alleen de actieve secties en hun geconfigureerde puntwaarden.
3. **Given** een deelnemer in een competitie, **When** een beheerder de betalingsstatus wijzigt, **Then** bewaart het systeem die status voor deelnamecontrole en markering.
4. **Given** meerdere beheerders in het systeem, **When** een beheerder probeert de laatste actieve beheerder te verwijderen, deactiveren of degraderen, **Then** weigert het systeem die wijziging.
5. **Given** een competitie met bestaande inzendingen of berekende standen, **When** een beheerder secties of puntregels wijzigt, **Then** blijft het systeem die wijzigingen toestaan en gebruikt het de actuele configuratie voor volgende berekeningen en publicatie.
6. **Given** een competitie zonder actieve secties, **When** een beheerder probeert die competitie actief te maken of open te stellen voor inzending, **Then** weigert het systeem die statuswijziging totdat minimaal één sectie actief is.
7. **Given** een competitie met een knock-out fase, **When** een beheerder de knock-out structuur configureert, **Then** kan deze meerdere rondes definiëren en per ronde de deelnemende teams invullen volgens het voor die ronde geldende aantal posities.
8. **Given** een geregistreerde gebruiker, **When** een beheerder deze aan een competitie toevoegt, **Then** wordt de gebruiker als deelnemer aan die competitie gekoppeld zonder extra codewijzigingen.
9. **Given** een open competitie, **When** een geregistreerde gebruiker zichzelf inschrijft, **Then** wordt die gebruiker als deelnemer aan die competitie gekoppeld zolang de competitie open is voor inzendingen.
10. **Given** een competitie met groepen, speelsteden en wedstrijden, **When** een beheerder die beheert via de beheer-UI, **Then** bewaart het systeem deze gegevens consistent voor voorspellingen, uitslagen en publieke wedstrijdinformatie.
11. **Given** een omgeving zonder shelltoegang, **When** een beheerder database-migraties of een handmatige standherberekening moet uitvoeren, **Then** biedt het systeem hiervoor een beschermde beheerworkflow.

---

### User Story 3 - Gast en deelnemer bekijken publieke standen en competitie-informatie (Priority: P3)

Als gast of deelnemer wil ik publieke competitie-informatie, uitslagen en standen kunnen bekijken, zodat ik inzicht heb in deelnemersaantallen, prijzengeld en ranglijsten zonder extra rechten nodig te hebben.

**Why this priority**: Publieke transparantie in standen en competitiegegevens is een expliciet succescriterium en verhoogt de bruikbaarheid van de applicatie voor alle betrokkenen.

**Independent Test**: Kan zelfstandig worden getest door zonder account de landingspagina en standenpagina's te openen en te controleren dat publieke data zichtbaar is, inclusief hoofdcompetities en sub-competities.

**Acceptance Scenarios**:

1. **Given** een actieve competitie, **When** een gast de publieke landingspagina opent, **Then** ziet deze de naam, het logo, de beschrijving, start- en einddatum, het aantal betaalde deelnemers, de totale inleg en het prijzengeld.
2. **Given** bestaande snapshots van een competitie of sub-competitie, **When** een gebruiker de standenlijst opent, **Then** ziet deze per deelnemer positie, naam, totaalpunten en positie-indicatoren zoals gestegen, gedaald, gelijk of nieuw.
3. **Given** een verwerkte uitslagwijziging die punten of rangorde verandert, **When** de standen opnieuw worden berekend, **Then** legt het systeem automatisch een nieuwe standensnapshot vast.
4. **Given** een sub-competitie met een subset van deelnemers, **When** een gebruiker de sub-competitiestand bekijkt, **Then** gebruikt het systeem dezelfde voorspellingen en punten als de hoofdcompetitie maar toont alleen de relevante deelnemers.
5. **Given** een publieke resultatenpagina, **When** een gebruiker wedstrijdinformatie bekijkt, **Then** toont het systeem ook de gekoppelde groep en speelstad waar beschikbaar.
6. **Given** een onbetaalde deelnemer, **When** een gast of andere deelnemer publieke standen of publieke deelnemersinformatie bekijkt, **Then** verschijnt die onbetaalde deelnemer daar niet totdat een beheerder de betalingsstatus op betaald zet.

---

### Edge Cases

- Het systeem weigert competitie-opslag of activatie wanneer de prijsverdeling niet exact 100% is.
- Het systeem weigert elke poging om na de deadline via UI, directe URL of handmatige request een voorspelling te wijzigen.
- Het systeem weigert bonusantwoorden die verwijzen naar inactieve of verwijderde entiteiten en dwingt een geldige actieve selectie af.
- Het systeem weigert knock-out configuraties en voorspellingen zodra een ronde minder of meer teams bevat dan voor die ronde is toegestaan.
- Als de knock-out structuur wijzigt nadat er al voorspellingen bestaan, markeert het systeem bestaande knock-out keuzes als ongeldig en dwingt het een hernieuwde volledige validatie af vóór een geldige definitieve inzending.
- Wanneer nog geen vorige snapshot bestaat, toont het systeem deelnemers als `nieuw`; een handmatige herberekening maakt altijd een nieuwe snapshot aan volgens dezelfde bewegingsregels.
- Wanneer een beheerder secties of puntregels wijzigt na eerdere inzendingen, toont het systeem deelnemers een zichtbare melding dat de configuratie is gewijzigd.
- Wanneer een sub-competitie geen deelnemers bevat, toont het systeem een lege standenweergave met duidelijke melding in plaats van een fout.
- De zichtbare markering voor onbetaalde deelnemers verschijnt alleen in beheercontext en in de private weergave van de betreffende deelnemer zelf.
- Onbetaalde deelnemers tellen niet mee in publieke standenlijsten of andere publieke deelnemersoverzichten totdat hun betalingsstatus op betaald staat.
- Zelfinschrijving is alleen toegestaan voor competities met status `open`; handmatige koppeling door een beheerder blijft altijd mogelijk binnen beheerrechten.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Het systeem MUST publieke bezoekers zonder account toegang geven tot de landingspagina, uitslagen en standen van competities.
- **FR-002**: Het systeem MUST deelnemeraccounts ondersteunen met minimaal voornaam, achternaam, uniek e-mailadres, telefoonnummer en een gehasht wachtwoord.
- **FR-003**: Het systeem MUST rollen en rechten afdwingen volgens de hiërarchie beheerder > deelnemer > gast.
- **FR-004**: Het systeem MUST verhinderen dat de laatste actieve beheerder wordt verwijderd, gedeactiveerd of gedegradeerd.
- **FR-005**: Het systeem MUST beheerders in staat stellen competities aan te maken, te wijzigen en de lifecycle-statussen `draft`, `active`, `open`, `closed` en `archived` te beheren volgens de vastgelegde toegangsregels.
- **FR-006**: Het systeem MUST per competitie naam, beschrijving, startdatum, einddatum, uiterste inleverdatum, actiefstatus, inlegbedrag, prijsverdeling en logo kunnen beheren.
- **FR-007**: Het systeem MUST valideren dat de prijsverdeling voor eerste, tweede en derde plaats samen exact 100% vormt.
- **FR-008**: Het systeem MUST per competitie configureerbare secties ondersteunen voor groepsfase uitslagen, winnaar/verliezer/gelijkspel, gele en rode kaarten, knock-out fase en bonusvragen.
- **FR-008a**: Het systeem MUST voor elke actieve wedstrijdgerelateerde sectie per relevante wedstrijd de bijbehorende voorspelling vereisen voordat een definitieve inzending mogelijk is.
- **FR-009**: Het systeem MUST per actieve sectie een configureerbare puntenwaarde ondersteunen.
- **FR-010**: Het systeem MUST de competitiestatussen als volgt afdwingen: `draft` = niet publiek en geen inzendingen; `active` = publiek zichtbaar; `open` = publiek zichtbaar en inzendingen toegestaan; `closed` = publiek zichtbaar maar alleen-lezen; `archived` = historisch en alleen-lezen.
- **FR-010a**: Het systeem MUST verhinderen dat een competitie `active` of `open` wordt gezet zolang er geen actieve secties zijn.
- **FR-011**: Het systeem MUST wedstrijden kunnen koppelen aan een groep en een speelstad en deze informatie tonen bij wedstrijdinformatie.
- **FR-012**: Het systeem MUST bonusvragen ondersteunen van het type entity-gebaseerd, numeriek en open tekst.
- **FR-012a**: Het systeem MUST bonusvraagondersteuning voor deelnemers scheiden van bonusvraagbeheer door beheerders: deelnemers beantwoorden alleen actieve bonusvragen binnen hun inzending, terwijl beheerders de bonusvraagdefinities en activatie beheren.
- **FR-013**: Het systeem MUST voor entity-gebaseerde bonusvragen alleen antwoorden accepteren die verwijzen naar actieve, valide entiteiten.
- **FR-014**: Het systeem MUST voor entity-gebaseerde vragen dropdown-selecties aanbieden.
- **FR-015**: Het systeem MUST bulkimport via CSV ondersteunen voor spelers en andere entiteiten.
- **FR-016**: Het systeem MUST een volledige CSV-import afwijzen zodra één rij ongeldig of dubbel is, en daarbij een duidelijke foutmelding met probleemrijen tonen.
- **FR-017**: Het systeem MUST per competitie een dynamisch aantal configureerbare knock-out rondes ondersteunen.
- **FR-018**: Het systeem MUST per knock-out ronde de deelnemende teams expliciet laten vastleggen.
- **FR-019**: Het systeem MUST per knock-out ronde valideren hoeveel teams toegestaan zijn op basis van de rondeconfiguratie.
- **FR-020**: Het systeem MUST per knock-out ronde volgorde, label en vereist aantal teamposities vastleggen en valideren.
- **FR-021**: Het systeem MUST deelnemers toestaan om per knock-out ronde voorspelde landen/teams in te vullen als onderdeel van hun definitieve competitievoorspelling.
- **FR-022**: Het systeem MUST bij knock-out voorspellingen alleen landen/teams accepteren die als actieve competitie-entiteiten beschikbaar zijn voor die competitie.
- **FR-023**: Het systeem MUST knock-out voorspellingen valideren op exact het vereiste aantal ingevulde landen/teams per ronde voordat een definitieve inzending mogelijk is.
- **FR-024**: Het systeem MUST deelnemers verplichten om alle actieve secties volledig in te vullen voordat een voorspelling definitief kan worden ingediend.
- **FR-025**: Het systeem MUST voorspellingen per deelnemer en competitie als een enkele definitieve inzending opslaan; gedeeltelijke inzendingen zijn niet toegestaan.
- **FR-026**: Het systeem MUST na de uiterste inleverdatum nieuwe inzendingen en wijzigingen blokkeren, de competitie-inzendstatus automatisch als `closed` behandelen en bestaande voorspellingen read-only tonen.
- **FR-027**: Het systeem MUST puntentelling volledig data-gedreven uitvoeren zonder hardcoded scoringsregels in de applicatielogica.
- **FR-028**: Het systeem MUST puntregels per competitie en per sectie configureerbaar maken.
- **FR-029**: Het systeem MUST beheerders toestaan sectie-instellingen en puntregels op elk moment te wijzigen, ook nadat deelnemers al hebben ingezonden.
- **FR-030**: Het systeem MUST sub-competities ondersteunen die behoren bij een hoofdcompetitie en een subset van deelnemers bevatten.
- **FR-031**: Het systeem MUST voor sub-competities dezelfde voorspellingen en puntresultaten gebruiken als voor de gekoppelde hoofdcompetitie.
- **FR-032**: Het systeem MUST automatisch een nieuwe standensnapshot opslaan na elke verwerkte uitslagwijziging die punten of rangorde verandert.
- **FR-032a**: Het systeem MUST publieke en interne standenweergaven opbouwen vanuit de laatst beschikbare relevante standensnapshot en niet vanuit live heraggregatie tijdens paginarendering.
- **FR-033**: Het systeem MUST beheerders een handmatige herberekening en nieuwe snapshot-creatie laten uitvoeren voor correcties of herstelacties.
- **FR-034**: Het systeem MUST per deelnemer in een standenlijst huidige positie, naam, totaalpunten, richting van positie-verandering en aantal plaatsen verschil tonen.
- **FR-035**: Het systeem MUST indicatoren voor gestegen, gedaald, gelijk en nieuw ondersteunen in hoofdcompetities, sub-competities en publieke weergaven.
- **FR-036**: Het systeem MUST beheerders per deelnemer een betalingsstatus laten vastleggen.
- **FR-037**: Het systeem MUST de betalingsstatus zichtbaar markeren zonder deelname of definitieve inzendingen van onbetaalde deelnemers te blokkeren.
- **FR-037a**: Het systeem MUST een duidelijke badge of label `Onbetaald` tonen aan beheerders en aan de betreffende deelnemer zelf in private views.
- **FR-037b**: Het systeem MUST onbetaalde deelnemers uitsluiten van publieke standenlijsten en andere publieke deelnemersoverzichten totdat hun betalingsstatus op betaald staat.
- **FR-038**: Het systeem MUST publieke competitie-overzichten tonen met naam, logo, beschrijving, start- en einddatum, aantal betaalde deelnemers, totale inleg en prijzengeldverdeling.
- **FR-039**: Het systeem MUST server-side rol- en rechtenchecks afdwingen voor alle beheer- en deelnemeracties.
- **FR-040**: Het systeem MUST CSRF-bescherming toepassen op muterende requests.
- **FR-041**: Het systeem MUST database-interacties uitvoeren via PDO met prepared statements.
- **FR-042**: Het systeem MUST server-side rendered pagina's leveren die bruikbaar zijn op desktop en mobiel.
- **FR-042a**: Het systeem MUST kernflows voor registratie, inloggen, voorspelling invullen, beheerformulieren en standenweergave bruikbaar maken op minimaal 360 px schermbreedte zonder horizontaal scrollen.
- **FR-042b**: Het systeem MUST formulieren toetsenbordtoegankelijk maken en voorzien van duidelijke labels en foutmeldingen voor verplichte velden en validatiefouten.
- **FR-043**: Het systeem MUST documentatie opleveren in Markdown onder `/docs` voor minimaal applicatiestart, architectuur en datamodel.
- **FR-044**: Het systeem MUST diagrammen in de documentatie in Mermaid notatie vastleggen.
- **FR-045**: Het systeem MUST worden ontwikkeld als een enkele codebase die meerdere toernooien kan ondersteunen.
- **FR-046**: Het systeem MUST testbaar zijn met unit tests voor domeinlogica en Playwright-validatie voor functionele UI-flows.
- **FR-047**: Het systeem MUST een beschermde beheerworkflow bieden voor migraties en handmatige standherberekening wanneer CLI/SSH op shared hosting niet beschikbaar is.
- **FR-047a**: Het systeem MUST via deze beschermde beheerworkflow minimaal database-migraties kunnen uitvoeren en een handmatige standherberekening kunnen starten.
- **FR-048**: Het systeem MUST beheerders bonusvragen laten aanmaken, wijzigen, activeren en deactiveren per competitie.
- **FR-049**: Het systeem MUST deelnemers bonusvragen laten beantwoorden als onderdeel van hun definitieve inzending.
- **FR-050**: Het systeem MUST bonusvraag-antwoorden valideren en meenemen in data-gedreven puntentelling.
- **FR-051**: Het systeem MUST groepen, speellocaties en wedstrijden beheerbaar maken via de beheer-UI; expliciet ondersteunde import mag aanvullend worden aangeboden maar vervangt de beheer-UI niet.
- **FR-052**: Het systeem MUST expliciet vastleggen hoe een gebruiker deelnemer van een competitie wordt.
- **FR-052a**: Het systeem MUST toestaan dat een geregistreerde gebruiker zichzelf inschrijft voor een competitie met status `open`.
- **FR-052b**: Het systeem MUST toestaan dat een beheerder een bestaande geregistreerde gebruiker handmatig aan een competitie koppelt.

### Key Entities *(include if feature involves data)*

- **Gebruiker**: Een persoon met een rol als gast, deelnemer of beheerder, met accountgegevens, authenticatiegegevens en statusinformatie.
- **Competitie**: Een toernooi-gebonden poule met metadata, datums, sectieconfiguratie, puntregels, prijsverdeling, deelnemers en publieke presentatiegegevens.
- **Sectie**: Een configureerbaar onderdeel van een competitie waarvoor voorspellingen worden ingevuld en punten worden toegekend.
- **Voorspelling**: De volledige, definitieve inzending van een deelnemer voor alle actieve secties van een competitie.
- **Wedstrijd**: Een speelmoment binnen een competitie, gekoppeld aan optionele groep en speelstad, met gegevens die relevant zijn voor voorspellingen en uitslagen.
- **Knock-out ronde**: Een configureerbare fase binnen de knock-out sectie van een competitie met een eigen volgorde, label en verwacht aantal teamposities.
- **Knock-out ronde team**: Een teamkoppeling binnen een specifieke knock-out ronde, gebruikt om per ronde de deelnemende teams vast te leggen.
- **Knock-out voorspelling**: De door een deelnemer ingevulde landen/teams per knock-out ronde als onderdeel van een definitieve inzending.
- **Bonusvraag**: Een aanvullende vraag binnen een competitie met een type, validatieregels en een deelnemerantwoord.
- **Entiteit**: Een selecteerbaar domeinobject voor bonusvragen, zoals land, team, speler of scheidsrechter.
- **Sub-competitie**: Een ranglijstgroep binnen een hoofdcompetitie die een subset van deelnemers gebruikt maar dezelfde voorspellingen en puntentelling deelt.
- **Standensnapshot**: Een vastgelegd momentbeeld van de rangorde en puntenstanden, gebruikt om positie-veranderingen te tonen.
- **Betalingsstatus**: De betaalindicatie van een deelnemer binnen een competitie, gebruikt voor markering of deelnamebeperking.

## Dependencies & Assumptions

- Actieve competitie-entiteiten vormen de enige geldige bron voor entity-gebaseerde bonusvragen en knock-out selecties.
- CSV-import gebruikt expliciet ondersteunde, door beheerders aangeleverde CSV-bestanden; de importtemplate en validatieregels bepalen welke kolommen verplicht zijn.
- Competitielogo's worden opgeslagen op het serverfilesystem in een niet-uitvoerbare uploadmap en alleen via opgeslagen relatieve paden gekoppeld.
- Publieke en private standenweergaven gebruiken altijd snapshotdata als bron voor rangorde en bewegingsindicatoren.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Een deelnemer kan voor een `open` competitie alle actieve secties in een enkele authenticated sessie afronden en definitief indienen; bij een onvolledige poging blokkeert het systeem de inzending en benoemt het de ontbrekende onderdelen.
- **SC-002**: Na het verstrijken van de uiterste inleverdatum accepteert het systeem in 100% van de gevallen geen nieuwe of gewijzigde voorspellingen meer.
- **SC-003**: Beheerders kunnen zonder codewijziging voor een nieuw toernooi competities, secties, puntwaarden, prijsverdeling, bonusvragen, knock-out rondes, groepen, speelsteden en wedstrijden configureren.
- **SC-004**: Publieke bezoekers kunnen zonder account de actuele competitie-informatie en standen bekijken voor hoofdcompetities en sub-competities, met alleen betaalde deelnemers in publieke standen en tellingen.
- **SC-005**: Standenlijsten tonen voor zowel hoofdcompetities als sub-competities reproduceerbare positie-indicatoren op basis van snapshots, inclusief gevallen gestegen, gedaald, gelijk en nieuw.
- **SC-006**: De oplossing blijft bruikbaar als een enkele codebase voor meerdere toernooien met configureerbare regels en uitbreidbare secties.

## Requirement Traceability

| Story | Kernfunctionaliteit | Belangrijkste requirements | Meetbare uitkomsten |
|-------|----------------------|----------------------------|---------------------|
| User Story 1 - Deelnemer levert volledige voorspelling in | Registratie/inloggen, volledige wedstrijd- en knock-outvoorspellingen, bonusvragen, definitieve inzending, deadline-locking | FR-002, FR-008, FR-008a, FR-012, FR-013, FR-014, FR-021, FR-022, FR-023, FR-024, FR-025, FR-026, FR-037, FR-049, FR-050, FR-052a | SC-001, SC-002 |
| User Story 2 - Beheerder beheert competities, deelnemers en puntregels | Competitielifecycle, secties en puntregels, bonusbeheer, betaling, enrollment, knock-outconfiguratie, beheer van groepen/speelsteden/wedstrijden, onderhoud zonder CLI | FR-004, FR-005, FR-006, FR-007, FR-008, FR-009, FR-010, FR-015, FR-016, FR-017, FR-018, FR-019, FR-020, FR-028, FR-029, FR-036, FR-047, FR-047a, FR-048, FR-051, FR-052, FR-052b | SC-003, SC-006 |
| User Story 3 - Gast en deelnemer bekijken publieke standen en competitie-informatie | Publieke competitie-overzichten, resultaten, snapshot-gedreven standen, sub-competities en bewegingsindicatoren | FR-001, FR-011, FR-030, FR-031, FR-032, FR-032a, FR-033, FR-034, FR-035, FR-038 | SC-004, SC-005 |
