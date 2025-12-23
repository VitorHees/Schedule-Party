# Schedule Party

**Schedule Party** is een uitgebreid platform voor evenementencoördinatie en gezamenlijke planning, gebouwd met Laravel 12, Livewire 3 en Tailwind CSS 4. Het stroomlijnt groepsplanning door gedeelde kalenders, beveiligde uitnodigingen en een geavanceerd permissiesysteem aan te bieden voor zowel privé-bijeenkomsten als professionele coördinatie.

## 🌟 Functies

* **Gezamenlijke Kalenders**: Maak persoonlijke schema's of gedeelde groepskalenders aan.
* **Geavanceerde Permissies**: Een systeem met drie niveaus (User Overrides > Groepspermissies > Rolpermissies) voor nauwkeurige toegangscontrole.
* **Beveiligde Uitnodigingen**: Genereer beveiligde tokens om deelnemers uit te nodigen via een Livewire-component.
* **Real-time Updates**: Geïntegreerd met Laravel Reverb voor live interactiviteit.
* **Locatiecoördinatie**: Beheer evenementdetails inclusief geografische coördinaten.
* **Rapportage**: Exporteer schema's en evenementgegevens naar PDF- of Excel-formaten.
* **Beveiliging**: Ingebouwde tweestapsverificatie (2FA), e-mailverificatie en authenticatie via Laravel Fortify.

## 🛠 Tech Stack

* **Backend**: Laravel 12
* **Frontend**: Livewire 3 (Volt & Flux), Tailwind CSS 4
* **Interactiviteit**: Alpine.js
* **Authenticatie**: Laravel Fortify
* **Testing**: Pest PHP

## 🚀 Setup & Installatie

### Vereisten
* PHP 8.3
* Composer & Node.js (NPM)
* Laravel Herd (Aanbevolen voor `.test` domeinen) of `php artisan serve`

### Installatiestappen

1.  **Project Clonen**:
    ```bash
    git clone <repository-url>
    cd schedule-party
    ```

2.  **Dependencies Installeren**:
    ```bash
    composer install
    npm install
    ```

3.  **Environment Setup**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Database Configuratie**:
    Het project gebruikt standaard SQLite. Voer de migraties en seeders uit voor de demo-data:
    ```bash
    php artisan migrate --seed
    ```

## 🏃 Applicatie Opstarten

### Met Laravel Herd (Aanbevolen)
Als je Laravel Herd gebruikt, wordt de webserver automatisch afgehandeld (bijv. op `http://schedule-party.test`). Je hoeft alleen de volgende commando's uit te voeren voor de assets en achtergrondtaken:

```bash
# Start Vite en de Queue Worker
npm run dev
php artisan queue:listen
```

### Standaard Setup
Als je geen Herd gebruikt, kun je het ingebouwde composer-shortcut gebruiken om de server, Vite en de queue tegelijkertijd te starten:

```bash
composer dev
```

*Let op: Zorg dat **Mailpit** draait om uitgaande e-mails en uitnodigingen op te vangen.*

## 🔐 Testaccount Gegevens
De database is gevuld met verschillende demo-accounts voor evaluatie:

| Gebruikersnaam | E-mailadres | Wachtwoord |
| :--- | :--- |:-----------|
| **John Doe** | `john@example.com` | `password` |
| **Sarah Smith** | `sarah@example.com` | `password` |
| **Mike Johnson** | `mike@example.com` | `password` |
| **Emily Davis** | `emily@example.com` | `password` |
| **Alex Brown** | `alex@example.com` | `password` |

*(Alle accounts gebruiken password als standaard wachtwoord.)*
