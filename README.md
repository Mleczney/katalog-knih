# Katalog knih

Webová aplikace pro správu katalogu knih. Po přihlášení může uživatel procházet katalog,
filtrovat ho podle autora a žánru, přidávat autory a přidávat nebo upravovat knihy.

## Verze

| Součást | Verze |
|---|---|
| PHP | 8.3 (Docker image `php:8.3-apache`) |
| Nette | 3.3 (nette/application 3.3, nette/forms 3.3, nette/database 3.2, nette/security 3.2) |
| Latte | 3.1 |
| Databáze | MariaDB 11.4 |
| Frontend | Bootstrap 5.3 (z CDN) |

Přesné verze knihoven jsou zamčené v `composer.lock`.

## Spuštění

Potřeba je jen Docker s Docker Compose. PHP ani Composer není nutné instalovat,
závislosti se nainstalují při sestavení image.

```bash
git clone https://github.com/Mleczney/katalog-knih.git
cd katalog-knih
docker compose up -d --build
```

Aplikace běží na **http://localhost:8080**.

První start trvá déle (stahují se image, instalují závislosti a inicializuje se databáze).
Aplikace se spustí až ve chvíli, kdy je databáze připravená (healthcheck).

### Testovací účet

| E-mail | Heslo |
|---|---|
| `test@example.com` | `Heslo123` |

### Zastavení a reset databáze

```bash
docker compose down        # zastaví kontejnery, data zůstanou
docker compose down -v     # zastaví kontejnery a smaže databázi
```

Po `down -v` a novém `up` se databáze vytvoří znovu i s ukázkovými daty.

## Konfigurace

Konfiguraci není potřeba měnit.

- **Připojení k databázi** je v `config/common.neon` (host `db`, databáze, uživatel i heslo `katalog`).
  Hodnoty odpovídají proměnným `MARIADB_*` v `docker-compose.yml`.
- **Vývojový režim** (ladicí lišta Tracy, podrobné chyby) je ve výchozím stavu vypnutý.
  Zapíná se proměnnou `APP_DEBUG=1`:
  ```bash
  APP_DEBUG=1 docker compose up -d
  ```
- Kód aplikace je součástí image, po změně kódu je proto potřeba `docker compose up -d --build`.

## Inicializace databáze

Databáze se inicializuje automaticky. Adresář `database/` je připojený do `/docker-entrypoint-initdb.d`
a MariaDB při prvním startu (s prázdným svazkem) spustí skripty v abecedním pořadí:

- `database/01-schema.sql` – tabulky, cizí klíče, unikátní klíče, kontrola roku,
- `database/02-seed.sql` – testovací účet a ukázková data ze zadání.

## Datový model

```
user                          (samostatně, pro přihlášení)
publisher 1 ──── N book
author    N ── book_author ── N book
genre     N ── book_genre  ── N book
```

- `book.publisher_id` je cizí klíč na `publisher`. Každá kniha má právě jedno nakladatelství.
- `book_author` a `book_genre` jsou propojovací tabulky se **složeným primárním klíčem**
  `(book_id, author_id)` / `(book_id, genre_id)`, který zajišťuje jedinečnost dvojic.
- Smazáním knihy zaniknou i její vazby (`ON DELETE CASCADE`). Autora, žánr ani nakladatelství,
  které jsou u nějaké knihy použité, smazat nelze (`ON DELETE RESTRICT`).
- `book.year` má kontrolu `CHECK (year > 0)`, `user.email` je unikátní.

## Hlavní rozhodnutí

- **Filtr katalogu** (`BookFacade::findCatalog`): filtr podle autora i žánru je samostatná podmínka `EXISTS`,
  ne JOIN na propojovací tabulky. Při obou filtrech musí kniha splnit oba, každá kniha je ve výsledku jen
  jednou a autoři a žánry se načítají zvlášť (dva dotazy pro všechny knihy). U knihy jsou proto vždy
  všichni její autoři a žánry, ne jen ten, podle kterého se filtrovalo.
- **Úprava knihy** (`BookFacade::update`): údaje knihy i vazby se ukládají v jedné transakci.
  Odebrané vazby se smažou, nové se vloží, ostatní zůstanou beze změny. Mažou se jen řádky v propojovací
  tabulce, autoři a žánry zůstávají. Při chybě se transakce vrátí celá.
- **Validace na serveru**: povinná pole, rok jako celé číslo 1–9999, popis nesmí být jen z mezer.
  Výběr nakladatelství odmítne hodnotu mimo nabídku, u autorů a žánrů se odeslané hodnoty porovnávají
  s nabídkou (`BookPresenter::validateChoices`). Poslední pojistkou jsou cizí klíče.
- **Hesla** se ukládají jako bcrypt hash (`password_hash()`), ověřují se přes `Nette\Security\Passwords`.
  Pro neznámý e-mail i špatné heslo je stejná hláška.
- **Kontrola přístupu**: všechny presentery kromě přihlášení dědí z `SecuredPresenter`, který ve `startup()`
  nepřihlášeného uživatele přesměruje na přihlášení. Platí to pro zobrazení stránek i odeslání formulářů.
  Po přihlášení se uživatel vrátí na stránku, kterou chtěl otevřít.
- **Formuláře** mají CSRF ochranu (`FormFactory`), odhlášení je signál, který Nette z cizího webu nepřijme.
- **ORM** (nepovinný bonus) není použité. Přístup k datům řeší Nette Database Explorer, katalog je
  psaný v SQL, aby bylo vidět, jak se pracuje s vazbami.

## Struktura projektu

```
app/
  Bootstrap.php            nastavení aplikace (debug režim, konfigurace)
  Core/RouterFactory.php   adresy: /prihlaseni, /autori, /kniha/nova, /kniha/<id>/upravit
  Forms/FormFactory.php    základ formulářů (CSRF, české hlášky)
  Model/                   BookFacade (katalog, ukládání knih), AuthorRepository, UserAuthenticator
  Presentation/            presentery a Latte šablony (Sign, Book, Author, SecuredPresenter, @layout.latte)
config/                    common.neon, services.neon
database/                  SQL schéma a ukázková data
www/                       veřejný adresář (index.php, .htaccess)
Dockerfile, docker-compose.yml
```

## Ověření podle zadání

1. Přihlaste se testovacím účtem.
2. Vyberte autora **Eva Malá** a žánr **Dobrodružný** → zobrazí se jen **Tichá řeka**, jednou,
   s oběma autory a oběma žánry.
3. U Tiché řeky klikněte na **Upravit**, odškrtněte **Jana Růžičku** a uložte → Jan Růžička zůstane
   autorem **Zápisků z pobřeží**.