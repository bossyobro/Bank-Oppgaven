# BankOppgave

## Brukerstøtte

Følg disse trinnene for å klone og sette opp prosjektet på Ubuntu Desktop:

1. **Installer nødvendige avhengigheter**:
   Åpne terminalen og kjør følgende kommandoer:
   ```bash
   sudo apt update
   sudo apt install git apache2 php php-mysql
   ```

2. **Klone prosjektet**:
   Naviger til katalogen der du vil lagre prosjektet, og klon det:
   ```bash
   git clone <repository-url>
   cd BankOppgave
   ```

3. **Kopier prosjektet til Apache-serveren**:
   Flytt prosjektfilene til Apache sin rotkatalog:
   ```bash
   sudo cp -r . /var/www/html/BankOppgave
   ```

4. **Gi nødvendige tillatelser**:
   Sørg for at Apache har tilgang til filene:
   ```bash
   sudo chown -R www-data:www-data /var/www/html/BankOppgave
   sudo chmod -R 755 /var/www/html/BankOppgave
   ```

5. **Start Apache-serveren**:
   Start eller restart Apache-serveren:
   ```bash
   sudo systemctl restart apache2
   ```

6. **Åpne prosjektet i nettleseren**:
   Gå til `http://localhost/BankOppgave` i nettleseren din for å se prosjektet.

Hvis du støter på problemer, sjekk Apache-loggene:
```bash
sudo tail -f /var/log/apache2/error.log
```
