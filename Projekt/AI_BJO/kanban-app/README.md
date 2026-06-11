# System Tablicy Kanban z Integracją GitHub – HIKIXMORI

Aplikacja webowa do zarządzania zadaniami w zespole, działająca na zasadzie tablicy Kanban. Pozwala ona organizować pracę pomiędzy trzema działami: writerów (pisarzy), grafików i programistów. Każdy dział ma swojego lidera, a nad całością czuwa administrator (CEO).

Głównym problemem, który rozwiązuje nasza aplikacja, jest brak spójnego narzędzia do koordynacji pracy w zespołach wielodyscyplinarnych. W wielu firmach i projektach studenckich zadania giną w komunikatorach, pliki latają po mailach, a nikt nie wie, co jest już zrobione, a co jeszcze czeka. Nasz system to rozwiązuje – wszystko jest w jednym miejscu, z jasnym podziałem na działy i statusy.

To co wyróżnia naszą aplikację od istniejących rozwiązań (np. Trello, Jira) to wbudowana integracja z GitHubem. Zatwierdzone pliki z ukończonych zadań mogą być automatycznie pushowane na odpowiednie gałęzie repozytorium GitHub, bez potrzeby znajomości Gita przez grafików czy pisarzy. System sam tworzy repozytoria, gałęzie i wysyła pliki – wystarczy jedno kliknięcie.

---

## Uruchomienie projektu (developer)

### Użyte technologie

| Technologia | Wersja | Strona |
|---|---|---|
| PHP | 8.2 | [php.net](https://www.php.net/) |
| Laravel | 12.62 | [laravel.com](https://laravel.com/) |
| PostgreSQL | 17 | [postgresql.org](https://www.postgresql.org/) |
| Composer | 2.x | [getcomposer.org](https://getcomposer.org/) |
| Tailwind CSS | 3.x (CDN) | [tailwindcss.com](https://tailwindcss.com/) |
| Alpine.js | 3.x (CDN) | [alpinejs.dev](https://alpinejs.dev/) |
| GitHub API | REST v3 | [docs.github.com](https://docs.github.com/en/rest) |

### Wymagania programowe

- **System operacyjny**: Windows 10/11, macOS lub Linux
- **Środowisko uruchomieniowe**: PHP 8.2+ z rozszerzeniami: `pdo_pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`
- **Composer**: v2.x – menedżer pakietów PHP
- **Silnik bazy danych**: PostgreSQL 14+ (testowane na 17)
- **Przeglądarka**: Dowolna nowoczesna przeglądarka (Chrome, Firefox, Edge)
- **Git** (opcjonalnie, do klonowania repozytorium)

### Instrukcja instalacji krok po kroku

1. **Sklonuj repozytorium** (lub rozpakuj archiwum z projektem):
```bash
git clone <adres-repozytorium>
cd kanban-app
```

2. **Zainstaluj zależności PHP**:
```bash
composer install
```

3. **Skopiuj plik konfiguracyjny i wygeneruj klucz aplikacji**:
```bash
cp .env.example .env
php artisan key:generate
```

4. **Skonfiguruj bazę danych** – edytuj plik `.env` i ustaw dane dostępowe do PostgreSQL:
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=kanban_db
DB_USERNAME=twoja_nazwa_uzytkownika
DB_PASSWORD=twoje_haslo
```

5. **Utwórz bazę danych** w PostgreSQL (np. przez pgAdmin lub terminal):
```sql
CREATE DATABASE kanban_db;
```

6. **Uruchom migracje** (tworzą wszystkie tabele w bazie):
```bash
php artisan migrate
```

7. **Uruchom serwer deweloperski**:
```bash
php artisan serve
```

8. Aplikacja będzie dostępna pod adresem: **http://127.0.0.1:8000**

### Pierwsze kroki po instalacji

Po uruchomieniu aplikacji przejdź na stronę rejestracji (`/register`). Pierwszy zarejestrowany użytkownik powinien zostać ręcznie zmieniony na administratora w bazie danych:

```sql
UPDATE users SET role = 'admin' WHERE id = 1;
```

Od tego momentu administrator może tworzyć konta liderów i zarządzać całym systemem z poziomu panelu administracyjnego.

---

## Podręcznik użytkownika

Poniżej opisano krok po kroku jak korzystać z aplikacji z perspektywy każdego typu użytkownika.

### Dostęp niezalogowanego użytkownika (gość)

Osoba niezalogowana **nie ma żadnego dostępu** do zasobów systemu. Próba wejścia na dowolną podstronę (np. tablicę Kanban, dashboard, panel zadań) skutkuje automatycznym przekierowaniem na stronę logowania (`/login`). System korzysta z middleware `auth` w Laravelu, który chroni wszystkie trasy oprócz logowania i rejestracji.

Dostępne strony dla gościa:
- `/login` – formularz logowania (email + hasło)
- `/register` – formularz rejestracji nowego konta

Na stronie rejestracji gość musi podać: imię i nazwisko, adres email, hasło (minimum 8 znaków, z potwierdzeniem) oraz obowiązkowo wybrać swoją specjalizację z listy rozwijanej (Writerzy / Graficy / Programiści). Po rejestracji użytkownik zostaje automatycznie zalogowany. Nie może przeglądać żadnych zasobów dopóki nie zostanie przypisany do projektu przez administratora.

### Korzystanie z systemu jako Użytkownik (rola: user)

Po zalogowaniu użytkownik trafia na **Dashboard** – panel główny ze statystykami dotyczącymi jego zadań w aktywnym projekcie.

**Co widzi użytkownik:**
- Dashboard z liczbą swoich zadań, ilością spóźnionych, podziałem na priorytety
- Tablicę Kanban – ale tylko kolumny związane z jego specjalizacją + „W trakcie", „Do zatwierdzenia", „Zrobione"
- Szczegóły zadań, do których jest przypisany (pełny opis, pliki, komentarze)
- Zadania innych użytkowników widzi na tablicy, ale ich opis jest ukryty za komunikatem „Pełny opis dostępny tylko dla przypisanych członków zespołu"

**Co może robić użytkownik:**
1. **Przeglądać tablicę Kanban** – widzi kafelki zadań ze skróconym opisem, priorytetem i oznaczeniem profesji
2. **Otwierać szczegóły swoich zadań** – klikając w kafelek przechodzi do pełnego widoku z opisem, plikami i komentarzami
3. **Zmieniać status swoich zadań** – np. przenieść z kolumny profesji do „W trakcie" lub „Do zatwierdzenia". Nie może przenieść do „Zrobione" – to robi lider
4. **Przesyłać pliki** – klikając „+ Dodaj plik" na stronie szczegółów zadania. Plik trafia ze statusem „Oczekujący" i czeka na zatwierdzenie przez lidera
5. **Usuwać swoje pliki** – może usunąć plik, który sam wrzucił
6. **Pisać komentarze** – pod każdym przypisanym zadaniem jest sekcja komentarzy
7. **Usuwać swoje komentarze** – może usunąć komentarz, który sam napisał
8. **Edytować swój profil** – zmiana imienia, emaila, hasła w zakładce „Profil"
9. **Przełączać projekty** – jeśli jest przypisany do wielu projektów, używa selektora w nagłówku
10. **Pobierać projekt z GitHuba** – klikając „Pobierz projekt (main)" w panelu bocznym

**Czego użytkownik NIE może robić:**
- Tworzyć, edytować ani usuwać zadań
- Zatwierdzać ani odrzucać plików
- Zarządzać innymi użytkownikami
- Przenosić zadań do kolumny „Zrobione"
- Pushować plików na GitHuba
- Przeglądać pełnych szczegółów zadań, do których nie jest przypisany

### Korzystanie z systemu jako Lider

Lider ma wszystkie możliwości zwykłego użytkownika, a dodatkowo posiada uprawnienia administracyjne ograniczone do swojego działu.

**Dodatkowe możliwości lidera:**
1. **Tworzenie zadań** – w zakładce „Zarządzaj zadaniami" → „Nowe zadanie". Formularz pozwala ustawić tytuł, opis, priorytet, termin i przypisać użytkowników ze swojego działu
2. **Edycja zadań** – może edytować zadania ze swojej kategorii (np. lider writerów edytuje tylko zadania writerów)
3. **Usuwanie zadań** – może usunąć zadania ze swojego działu (przycisk kosza w widoku szczegółów lub na liście)
4. **Zatwierdzanie/odrzucanie plików** – na stronie szczegółów zadania widzi przyciski ✓ (zatwierdź) i ✕ (odrzuć) przy plikach ze statusem „Oczekujący". Przy odrzuceniu może wpisać feedback z powodem
5. **Przenoszenie do „Zrobione"** – tylko lider (lub admin) może przenieść zadanie do kolumny Zrobione
6. **Przeciąganie kafelków (drag & drop)** – na tablicy Kanban może przeciągać zadania ze swojej profesji między dozwolonymi kolumnami
7. **Push na GitHuba** – na stronie ukończonego zadania pojawia się przycisk „Wypchnij pliki (Push)" do wysłania zatwierdzonych plików na odpowiednią gałąź

### Korzystanie z systemu jako Administrator (CEO)

Administrator ma pełny, nieograniczony dostęp do całego systemu. Widzi wszystkie zadania, wszystkich użytkowników, wszystkie projekty.

**Panel administracyjny (sidebar → Zarządzanie):**

1. **Zarządzaj zadaniami** – pełna lista wszystkich zadań z filtrami (szukaj, status, profesja, priorytet, przypisany użytkownik, data). Sortowanie po tytule, statusie, priorytecie, terminie. Kliknięcie w wiersz otwiera szczegóły zadania. Przyciski edycji i usuwania w każdym wierszu.
2. **Zarządzaj użytkownikami** – lista wszystkich użytkowników z możliwością:
   - Tworzenia nowych kont (w tym kont liderów – niedostępnych z publicznej rejestracji)
   - Edycji danych użytkowników (imię, email, rola, specjalizacja, przypisanie do projektów)
   - Aktywacji / dezaktywacji kont (przycisk toggle – nieaktywni użytkownicy nie mogą się logować i nie pojawiają się na listach przypisań)
   - Usuwania kont
3. **Zarządzaj projektami** – tworzenie, edycja, usuwanie projektów. Przy tworzeniu można od razu skonfigurować integrację z GitHubem (token + nazwa repo) i automatycznie utworzyć repozytorium.

---

## Udokumentowany CRUD – Zadania (Tasks)

Zadania to główny zasób systemu. Każde zadanie jest zależne od projektu (relacja many-to-one: wiele zadań należy do jednego projektu) oraz powiązane z użytkownikami (relacja many-to-many: do zadania można przypisać wielu użytkowników).

### CREATE – Tworzenie nowego zadania

**Ścieżka:** Panel boczny → Zarządzaj zadaniami → przycisk „Nowe zadanie"  
**Dostęp:** Administrator, Lider (tylko w swojej kategorii)  
**Kontroler:** `Admin\TaskController@create` i `Admin\TaskController@store`

**Pola formularza:**

| Pole | Typ pola | Walidacja serwera | Walidacja klienta | Opis |
|---|---|---|---|---|
| Tytuł | `<input type="text">` | `required`, `string`, `min:3`, `max:255` | atrybut `required`, `minlength="3"`, `maxlength="255"` | Krótki tytuł zadania |
| Opis | `<textarea>` | `required`, `string`, `min:10` | atrybut `required`, `minlength="10"` | Szczegółowy opis zadania. System automatycznie generuje skrócony opis (100 znaków) do wyświetlenia na kafelku |
| Kategoria/Status | `<select>` z listą rozwijaną | `required`, `in:writers,graphics,programmers` | atrybut `required` | Wybór działu, do którego należy zadanie. Lider widzi tylko swoją kategorię. Admin widzi wszystkie trzy |
| Priorytet | `<select>` z listą rozwijaną | `required`, `in:low,medium,high` | atrybut `required` | Wybór priorytetu: Niski / Średni / Wysoki |
| Termin wykonania | `<input type="date">` | `nullable`, `date`, `after_or_equal:today` | atrybut `type="date"` | Opcjonalny termin. Jeśli podany, nie może być datą z przeszłości |
| Przypisani użytkownicy | Lista checkboxów | `nullable`, `array`, każdy element `exists:users,id` | brak (opcjonalne pole) | Lista aktywnych użytkowników przypisanych do bieżącego projektu. Lider widzi tylko użytkowników ze swojej specjalizacji. Admin widzi wszystkich. Nieaktywni użytkownicy nie pojawiają się na liście |

**Walidacja po stronie serwera (Laravel):**
Reguły walidacji są zdefiniowane w metodzie `store()` kontrolera `Admin\TaskController`. Gdy walidacja nie przejdzie, Laravel automatycznie przekierowuje z powrotem na formularz z komunikatami błędów wyświetlanymi w języku polskim pod każdym polem. Komunikaty są niestandardowe (zdefiniowane w tablicy `$messages`), np.:
- „Tytuł zadania jest wymagany."
- „Tytuł musi mieć co najmniej 3 znaki."
- „Opis musi mieć co najmniej 10 znaków."
- „Termin nie może być w przeszłości."

**Walidacja po stronie klienta (HTML5):**
Formularze korzystają z natywnej walidacji HTML5 poprzez atrybuty `required`, `minlength`, `maxlength`, `type="date"`. Przeglądarka blokuje wysłanie formularza, jeśli wymagane pole jest puste lub nie spełnia ograniczeń.

**Logika biznesowa przy tworzeniu:**
- Pole `profession` jest automatycznie ustawiane na wartość wybranego statusu (np. jeśli wybrano „Graficy", profession = `graphics`)
- Pole `description_short` jest generowane automatycznie jako pierwsze 100 znaków opisu
- Pole `created_by` jest ustawiane na ID zalogowanego użytkownika
- Pole `project_id` jest pobierane z sesji (aktywny projekt)
- Jeśli lider próbuje ustawić kategorię inną niż swoją, serwer zwraca błąd walidacji

### READ – Lista zadań z filtrowaniem i sortowaniem

**Ścieżka:** Panel boczny → Zarządzaj zadaniami  
**Dostęp:** Administrator, Lider  
**Kontroler:** `Admin\TaskController@index`

**Wyświetlane kolumny w tabeli:**

| Kolumna | Opis |
|---|---|
| Tytuł | Nazwa zadania + skrócony opis pod spodem |
| Status | Kolorowa etykieta (np. fioletowa „Writerzy", różowa „Graficy") |
| Priorytet | Kolorowa etykieta (czerwona „Wysoki", żółta „Średni", zielona „Niski") |
| Przypisani | Awatary (inicjały) przypisanych użytkowników, max 3 widoczne + licznik reszty |
| Termin | Data w formacie dd.mm.YYYY, czerwona jeśli po terminie |
| Pliki / Komentarze | Liczniki: 📎 X / 💬 Y |
| Akcje | Przyciski: Edytuj (ikona ołówka), Usuń (ikona kosza) |

**Dostępne filtry:**

| Filtr | Typ | Opis |
|---|---|---|
| Szukaj | Pole tekstowe | Wyszukiwanie po tytule i opisie zadania |
| Status | Lista rozwijana | Filtrowanie po statusie: Writerzy, Graficy, Programiści, W trakcie, Do zatwierdzenia, Zrobione |
| Profesja | Lista rozwijana | Filtrowanie po typie zadania: Writerzy, Graficy, Programiści |
| Priorytet | Lista rozwijana | Filtrowanie po priorytecie: Niski, Średni, Wysoki |
| Użytkownik | Lista rozwijana | Filtrowanie po przypisanym użytkowniku (lista wszystkich aktywnych użytkowników projektu) |
| Data od | Pole daty | Zadania utworzone od podanej daty |

Filtry można łączyć ze sobą (np. „pokaż tylko zadania grafików o wysokim priorytecie przypisane do Jana Kowalskiego"). Przycisk „Wyczyść" resetuje wszystkie filtry.

**Sortowanie:**
Kliknięcie w nagłówek kolumny sortuje dane rosnąco/malejąco. Sortowalne kolumny: Tytuł, Status, Priorytet, Termin. Strzałka ↑/↓ wskazuje aktualny kierunek sortowania.

**Paginacja:**
Lista jest podzielona na strony po 15 zadań. Pod tabelą wyświetla się nawigacja stron.

**Kliknięcie w wiersz:**
Kliknięcie w dowolne miejsce w wierszu (poza przyciskami akcji) otwiera pełny widok szczegółów zadania – taki sam jak z tablicy Kanban.

### UPDATE – Edycja zadania

**Ścieżka:** Panel boczny → Zarządzaj zadaniami → ikona ołówka przy zadaniu, lub widok szczegółów → przycisk „Edytuj"  
**Dostęp:** Administrator (wszystkie zadania), Lider (tylko zadania ze swojej kategorii)  
**Kontroler:** `Admin\TaskController@edit` i `Admin\TaskController@update`

Formularz edycji jest identyczny z formularzem tworzenia, z tą różnicą, że:
- Pola są wypełnione aktualnymi danymi zadania
- Checkboxy przypisanych użytkowników mają zaznaczonych aktualnych członków zespołu
- Pole „Termin wykonania" dopuszcza daty z przeszłości (żeby nie blokować edycji starych zadań)
- Zmiana statusu na jedną z profesji (writers/graphics/programmers) automatycznie aktualizuje pole `profession`

**Walidacja:**
Identyczna jak przy tworzeniu – te same reguły po stronie serwera i klienta. Dodatkowe sprawdzenie uprawnień: jeśli lider próbuje edytować zadanie z innej kategorii, otrzymuje komunikat flash „Brak uprawnień do edycji zadania z innej kategorii" i zostaje przekierowany z powrotem.

### DELETE – Usuwanie zadania

**Ścieżka:** Ikona kosza na liście zadań lub przycisk „Usuń" w widoku szczegółów  
**Dostęp:** Administrator (wszystkie zadania), Lider (tylko zadania ze swojej kategorii)  
**Kontroler:** `Admin\TaskController@destroy` oraz `TaskController@destroy`

**Zabezpieczenia:**
- Przed usunięciem wyświetla się okno potwierdzenia JavaScript: „Czy na pewno chcesz usunąć to zadanie?"
- Po stronie serwera sprawdzane są uprawnienia przez `TaskPolicy@delete` – jeśli użytkownik nie ma prawa usunąć tego zadania, otrzymuje komunikat flash o braku uprawnień (zamiast strony błędu 403)
- Zadanie jest usuwane „miękko" (soft delete) – nie jest fizycznie kasowane z bazy, tylko oznaczane jako usunięte (kolumna `deleted_at`). Dzięki temu w razie pomyłki można je odzyskać
- Formularz DELETE korzysta z ochrony CSRF (token `@csrf`) i metody HTTP DELETE (`@method('DELETE')`)

---

## Udokumentowany CRUD – Użytkownicy (tylko Administrator)

Drugi zasób z pełnym CRUD-em to zarządzanie użytkownikami, dostępne wyłącznie dla administratora.

### CREATE – Tworzenie nowego użytkownika

**Ścieżka:** Panel boczny → Zarządzaj użytkownikami → przycisk „Nowy użytkownik"  
**Dostęp:** tylko Administrator

**Pola formularza:**

| Pole | Typ pola | Walidacja | Opis |
|---|---|---|---|
| Imię i nazwisko | `<input type="text">` | `required`, `string`, `max:255` | Pełna nazwa użytkownika |
| Email | `<input type="email">` | `required`, `email`, `unique:users` | Musi być unikatowy w systemie |
| Hasło | `<input type="password">` | `required`, `min:8`, `confirmed` | Minimum 8 znaków, z potwierdzeniem |
| Potwierdź hasło | `<input type="password">` | musi być identyczne z hasłem | Zabezpieczenie przed literówką |
| Rola | `<select>` | `required`, `in:user,leader` | Administrator wybiera z listy. Nie można stworzyć drugiego admina z tego formularza |
| Specjalizacja | `<select>` | `required`, `in:writers,graphics,programmers` | Wybór działu: Writerzy / Graficy / Programiści |
| Projekty | Lista checkboxów | `nullable`, `array` | Lista wszystkich projektów – admin zaznacza, do których projektów przypisać użytkownika |

To jest jedyny sposób na stworzenie konta z rolą **Lider** – publiczna rejestracja pozwala wyłącznie na tworzenie kont z rolą „user".

### READ – Lista użytkowników

**Ścieżka:** Panel boczny → Zarządzaj użytkownikami  
**Dostęp:** tylko Administrator

Wyświetla tabelę ze wszystkimi użytkownikami: imię, email, rola, specjalizacja, status (aktywny/nieaktywny), data rejestracji. Przy każdym użytkowniku są przyciski: Edytuj, Przełącz aktywność, Usuń. Lista jest stronicowana po 15 pozycji.

### UPDATE – Edycja użytkownika

Administrator może zmienić: imię, email, rolę, specjalizację, przypisane projekty. Hasło można zmienić opcjonalnie (jeśli pole jest puste, stare hasło zostaje). Walidacja identyczna jak przy tworzeniu, z wyjątkiem: email musi być unikatowy z pominięciem aktualnego użytkownika (`unique:users,email,{id}`), hasło jest opcjonalne.

### DELETE – Usuwanie użytkownika

Usunięcie z potwierdzeniem JavaScript. Formularz chroniony tokenem CSRF i metodą DELETE. Jeśli usuwany użytkownik jest aktualnie aktywnym projektem w sesji admina, sesja jest czyszczona.

### Aktywacja/dezaktywacja kont

Dodatkowa operacja: administrator może jednym kliknięciem przełączyć konto na aktywne/nieaktywne. Nieaktywni użytkownicy:
- Nie mogą się zalogować
- Nie pojawiają się na listach przypisań przy tworzeniu zadań
- Ich konta istnieją w systemie, ale są „zamrożone"

---

## Role użytkowników i uprawnienia – szczegółowe zestawienie

### Tabela uprawnień

| Operacja | Gość (niezalogowany) | Użytkownik | Lider | Administrator |
|---|---|---|---|---|
| Przeglądanie strony logowania/rejestracji | ✅ | — | — | — |
| Przeglądanie tablicy Kanban | ❌ | ✅ (swoje kolumny) | ✅ (wszystkie) | ✅ (wszystkie) |
| Przeglądanie szczegółów zadania | ❌ | ✅ (tylko swoje) | ✅ (swoja kategoria) | ✅ (wszystkie) |
| Pełny opis zadania | ❌ | ✅ (jeśli przypisany) | ✅ (swoja kategoria) | ✅ |
| Tworzenie zadań | ❌ | ❌ | ✅ (swoja kategoria) | ✅ |
| Edycja zadań | ❌ | ❌ | ✅ (swoja kategoria) | ✅ |
| Usuwanie zadań | ❌ | ❌ | ✅ (swoja kategoria) | ✅ |
| Zmiana statusu zadania | ❌ | ✅ (swoje) | ✅ (swoja kategoria) | ✅ |
| Przeniesienie do „Zrobione" | ❌ | ❌ | ✅ (swoja kategoria) | ✅ |
| Przesyłanie plików | ❌ | ✅ (swoje zadania) | ✅ | ✅ |
| Zatwierdzanie/odrzucanie plików | ❌ | ❌ | ✅ (swoja kategoria) | ✅ |
| Usuwanie plików | ❌ | ✅ (swoich) | ✅ (swoja kategoria) | ✅ |
| Pisanie komentarzy | ❌ | ✅ | ✅ | ✅ |
| Push na GitHuba | ❌ | ❌ | ✅ (swoja kategoria) | ✅ |
| Pobieranie projektu z GitHuba | ❌ | ✅ | ✅ | ✅ |
| Zarządzanie użytkownikami | ❌ | ❌ | ❌ | ✅ |
| Zarządzanie projektami | ❌ | ❌ | ❌ | ✅ |
| Tworzenie kont liderów | ❌ | ❌ | ❌ | ✅ |
| Aktywacja/dezaktywacja kont | ❌ | ❌ | ❌ | ✅ |
| Edycja swojego profilu | ❌ | ✅ | ✅ | ✅ |

### Zarządzanie zasobami przez użytkowników

Każdy zalogowany użytkownik może zarządzać swoimi zasobami:

**Pliki** – użytkownik przesyła pliki do zadań, do których jest przypisany. Może usunąć pliki, które sam wgrał. Nie może usuwać plików wrzuconych przez innych. Przesłane pliki mają status „Oczekujący" i czekają na zatwierdzenie przez lidera.

**Komentarze** – użytkownik może dodawać komentarze pod swoimi zadaniami i usuwać wyłącznie swoje komentarze. Nie może edytować ani usuwać komentarzy innych osób.

**Profil** – w zakładce „Profil" (ikona użytkownika w panelu bocznym) każdy użytkownik może zmienić swoje imię, adres email oraz hasło. Zmiana hasła wymaga podania aktualnego hasła oraz nowego hasła z potwierdzeniem (minimum 8 znaków).

### Zarządzanie profilami użytkowników przez Administratora

Administrator ma pełną kontrolę nad kontami użytkowników z poziomu zakładki „Zarządzaj użytkownikami":
- **Tworzenie kont** – w tym kont Liderów, które nie są dostępne z publicznej rejestracji
- **Edycja danych** – może zmienić imię, email, rolę, specjalizację i przypisane projekty dowolnego użytkownika
- **Zmiana hasła** – może ustawić nowe hasło użytkownikowi (opcjonalnie, jeśli zostawi pole puste – hasło się nie zmieni)
- **Aktywacja/dezaktywacja** – jednym kliknięciem „zamraża" konto. Nieaktywny użytkownik nie może się zalogować
- **Usuwanie kont** – trwałe usunięcie konta z systemu z potwierdzeniem

---

## Zaawansowana logika biznesowa (na ocenę 5.0)

### Integracja z GitHub API

To najważniejsza i najbardziej zaawansowana funkcja systemu, wykraczająca daleko poza prosty CRUD.

**Jak to działa:**

1. **Konfiguracja repozytorium** – Administrator przy tworzeniu/edycji projektu podaje Personal Access Token z GitHuba i nazwę repozytorium. Może zaznaczyć checkbox „Utwórz automatycznie" – wtedy system łączy się z GitHub API (`POST /user/repos`) i automatycznie tworzy publiczne repozytorium z plikiem README.

2. **Automatyczna sanityzacja** – Jeśli admin wklei pełny link (np. `https://github.com/user/repo`), system automatycznie wyciąga z niego samą część `user/repo`. Obsługuje też format `.git`.

3. **Push plików na gałąź** – Gdy zadanie ze statusem „Zrobione" zawiera zatwierdzone pliki, lider lub admin może kliknąć przycisk „Wypchnij pliki (Push)". System:
   - Sprawdza, czy na GitHubie istnieje gałąź odpowiadająca profesji (np. `writers`, `graphics`, `programmers`)
   - Jeśli repozytorium jest puste (błąd 409), automatycznie inicjalizuje je plikiem README
   - Jeśli gałąź nie istnieje, pobiera SHA ostatniego commita z `main` (lub `master`) i tworzy nową gałąź
   - Konwertuje każdy zatwierdzony plik na Base64 i wysyła go przez GitHub API (`PUT /contents/{path}`)
   - Pliki lądują w folderach o czytelnej nazwie: `Zadania/{id}-{slug-tytulu}/nazwa-pliku.ext`

4. **Pobieranie projektu** – Każdy członek zespołu może pobrać aktualny kod z gałęzi `main` jako archiwum ZIP, klikając „Pobierz projekt (main)" w panelu bocznym. System obsługuje zarówno publiczne repo (bezpośredni redirect), jak i prywatne (pobieranie przez token po stronie serwera).

### System weryfikacji plików z feedbackiem

Pełny przepływ pracy nad plikiem:
1. Użytkownik przesyła plik → status: **Oczekujący** (żółty)
2. Lider klika ✓ → status: **Zatwierdzony** (zielony) – plik gotowy do pusha na GitHuba
3. Lider klika ✕ → status: **Odrzucony** (czerwony) z opcjonalnym komentarzem dlaczego (np. „Zły format, proszę o PNG zamiast BMP")
4. Użytkownik widzi feedback bezpośrednio pod plikiem i wie co poprawić

To nie jest prosty CRUD na plikach – to pełny cykl recenzji z wieloma aktorami i zmianami stanów.

### Multiprojektowość

System obsługuje wiele niezależnych projektów z pełną izolacją danych:
- Każdy projekt ma własną tablicę Kanban, własne zadania, własnych użytkowników
- Użytkownicy mogą być przypisani do wielu projektów jednocześnie
- Przełączanie między projektami odbywa się w selektorze w nagłówku
- Dashboard, statystyki i filtry automatycznie dostosowują się do aktywnego projektu
- Każdy projekt może mieć własne repozytorium GitHub

### System powiadomień

Automatyczne powiadomienia w czasie rzeczywistym:
- Gdy zadanie zostanie przeniesione do „Do zatwierdzenia" → powiadomienie do lidera działu i admina
- Gdy ktoś doda komentarz → powiadomienie do wszystkich przypisanych osób i lidera
- Powiadomienia wyświetlane w dashboardzie z możliwością odrzucenia
- Kliknięcie w powiadomienie przenosi do odpowiedniego zadania

### Drag & drop na tablicy Kanban

Liderzy mogą przeciągać kafelki zadań między kolumnami na tablicy Kanban. System waliduje uprawnienia zarówno po stronie klienta (JavaScript), jak i po stronie serwera (kontroler sprawdza, czy użytkownik ma prawo przenieść zadanie do danej kolumny). Zwykli użytkownicy nie mogą przeciągać kafelków – zmieniają status przez przyciski na stronie szczegółów.

---

## Struktura bazy danych

### Diagram relacji (ERD)

```
users ──────┬──── project_user ────── projects
            │        (M:N)
            ├──── task_user ────── tasks
            │        (M:N)        │  (N:1 → projects)
            ├──── comments ───────┤  (N:1 → tasks)
            │                     │
            └──── task_files ─────┘  (N:1 → tasks)

            notifications (polimorficzne → users)
```

**Relacje:**
- `projects` ↔ `users` – wiele-do-wielu (przez `project_user`)
- `projects` → `tasks` – jeden-do-wielu (projekt ma wiele zadań)
- `tasks` ↔ `users` – wiele-do-wielu (przez `task_user`, przypisanie do zadań)
- `tasks` → `task_files` – jeden-do-wielu (zadanie ma wiele plików)
- `tasks` → `comments` – jeden-do-wielu (zadanie ma wiele komentarzy)
- `users` → `task_files` – jeden-do-wielu (użytkownik przesyła pliki)
- `users` → `comments` – jeden-do-wielu (użytkownik pisze komentarze)
- `users` → `tasks` (created_by) – jeden-do-wielu (twórca zadania)

---

## Struktura plików projektu

```
kanban-app/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/
│   │   │   ├── ProjectController.php    # CRUD projektów + auto-tworzenie repo GitHub
│   │   │   ├── TaskController.php       # CRUD zadań (panel admina/lidera)
│   │   │   └── UserController.php       # CRUD użytkowników + toggle aktywności
│   │   ├── Auth/
│   │   │   └── AuthController.php       # Logowanie, rejestracja, wylogowanie
│   │   ├── CommentController.php        # Dodawanie i usuwanie komentarzy
│   │   ├── DashboardController.php      # Dashboard + przełączanie projektów + pobieranie ZIP
│   │   ├── FileController.php           # Upload, download, zatwierdzanie, odrzucanie, usuwanie plików
│   │   ├── NotificationController.php   # Oznaczanie powiadomień jako przeczytane
│   │   ├── ProfileController.php        # Edycja profilu i zmiana hasła
│   │   └── TaskController.php           # Tablica Kanban, zmiana statusu, drag&drop, push GitHub
│   ├── Models/
│   │   ├── Comment.php                  # Model komentarza
│   │   ├── Project.php                  # Model projektu (+ github_repo, github_token)
│   │   ├── Task.php                     # Model zadania (statusy, priorytety, relacje)
│   │   ├── TaskFile.php                 # Model pliku (statusy: pending/approved/rejected)
│   │   └── User.php                     # Model użytkownika (role, specjalizacje, metody pomocnicze)
│   ├── Notifications/
│   │   ├── NewCommentNotification.php   # Powiadomienie o nowym komentarzu
│   │   └── TaskToApproveNotification.php # Powiadomienie o zadaniu do zatwierdzenia
│   └── Policies/
│       └── TaskPolicy.php               # Reguły uprawnień: kto może co robić z zadaniami
├── database/
│   ├── factories/
│   │   └── UserFactory.php              # Fabryka do generowania testowych użytkowników
│   └── migrations/                      # 13 migracji tworzących cały schemat bazy danych
├── resources/views/
│   ├── admin/
│   │   ├── projects/                    # Widoki: lista, tworzenie, edycja projektów
│   │   ├── tasks/                       # Widoki: lista, tworzenie, edycja zadań
│   │   └── users/                       # Widoki: lista, tworzenie, edycja użytkowników
│   ├── auth/
│   │   ├── login.blade.php              # Formularz logowania
│   │   └── register.blade.php           # Formularz rejestracji
│   ├── board/
│   │   ├── index.blade.php              # Tablica Kanban z kolumnami i drag&drop
│   │   └── show.blade.php               # Szczegóły zadania (pliki, komentarze, statusy, push)
│   ├── layouts/
│   │   └── app.blade.php                # Główny layout (sidebar, nawigacja, selektor projektów)
│   ├── profile/
│   │   └── edit.blade.php               # Edycja profilu użytkownika
│   └── dashboard.blade.php              # Dashboard ze statystykami
├── routes/
│   └── web.php                          # Definicje wszystkich ścieżek URL z middleware
└── .env                                 # Konfiguracja środowiskowa (baza, klucze, itp.)
```
