<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use App\Models\Comment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin (CEO)
        $admin = User::create([
            'name' => 'Administrator CEO',
            'email' => 'admin@kanban.pl',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'specialization' => null,
            'is_active' => true,
        ]);

        // Leaders
        $leaderWriters = User::create([
            'name' => 'Anna Kowalska',
            'email' => 'lider.writers@kanban.pl',
            'password' => Hash::make('lider123'),
            'role' => 'leader',
            'specialization' => 'writers',
            'is_active' => true,
        ]);

        $leaderGraphics = User::create([
            'name' => 'Marek Nowak',
            'email' => 'lider.graphics@kanban.pl',
            'password' => Hash::make('lider123'),
            'role' => 'leader',
            'specialization' => 'graphics',
            'is_active' => true,
            'name' => 'Piotr Wiśniewski',
            'email' => 'leader.graphics@kanban.local',
            'password' => Hash::make('haslo123'),
            'role' => 'leader',
            'specialization' => 'graphics',
            'is_active' => true,
        ]);
        $leaderGraphics->projects()->attach($mainProject);

        $leaderProgrammers = User::create([
            'name' => 'Michał Kaczmarek',
            'email' => 'leader.programmers@kanban.local',
            'password' => Hash::make('haslo123'),
            'role' => 'leader',
            'specialization' => 'programmers',
            'is_active' => true,
        ]);
        $leaderProgrammers->projects()->attach($mainProject);

        // Użytkownicy (Pracownicy)
        $userPB = User::create([
            'name' => 'Basia Kamińska',
            'email' => 'basia@kanban.local',
            'password' => Hash::make('haslo123'),
            'role' => 'user',
            'specialization' => 'writers',
            'is_active' => true,
        ]);
        $userPB->projects()->attach($mainProject);

        $userBB = User::create([
            'email' => 'bb@kanban.pl',
            'password' => Hash::make('user123'),
            'role' => 'user',
            'specialization' => 'graphics',
            'is_active' => true,
        ]);

        $userOG = User::create([
            'name' => 'Olga G.',
            'email' => 'og@kanban.pl',
            'password' => Hash::make('user123'),
            'role' => 'user',
            'specialization' => 'programmers',
            'is_active' => true,
        ]);

        // Sample tasks
        $task1 = Task::create([
            'title' => 'Kolędnicy-kultyści',
            'description' => 'Opis wzualny każdego z nich, motywacja wizyty, warianty dialogu dla każdego z osobna, jakie subtelnie sygnały pomogą graczom ich rozróżnić od zwykłych kolędników? Przygotować co najmniej 3 warianty dialogu dla każdej z postaci.',
            'description_short' => 'Opis wizualny, motywacja, warianty dialogu',
            'status' => 'in_progress',
            'priority' => 'high',
            'created_by' => $admin->id,
            'due_date' => now()->addDays(3),
        ]);
        $task1->assignees()->attach([$userPB->id, $userBB->id]);

        $task2 = Task::create([
            'title' => 'Sortowanie plików',
            'description' => 'Zasady sortowania plików, UI, poziomy trudności, czy praca staje się coraz trudniejsza przy niskiej poczytalności? Opracować system progresji trudności.',
            'description_short' => 'Zasady sortowania, UI, poziomy trudności',
            'status' => 'done',
            'priority' => 'medium',
            'created_by' => $leaderProgrammers->id,
            'due_date' => now()->subDays(2),
        ]);
        $task2->assignees()->attach([$userOG->id]);

        $task3 = Task::create([
            'title' => 'Dziewczyna z węzłem',
            'description' => 'Opis wizualny postaci, motywacja wizyty, 3 warianty dialogu, jakie pozory sprawia, jaka się okaże? Dopracować szczegóły wyglądu i osobowości.',
            'description_short' => 'Opis wizualny, motywacja wizyty, 3 warianty dialogu',
            'status' => 'done',
            'priority' => 'medium',
            'created_by' => $leaderWriters->id,
            'due_date' => now()->subDays(5),
        ]);
        $task3->assignees()->attach([$userBB->id]);

        $task4 = Task::create([
            'title' => 'Kurier z jedzeniem',
            'description' => 'Opis wizualny, 3 warianty dialogu, jak na psychikę będzie wpływać jedyna interakcja "konieczna"? Przeanalizować wpływ każdej opcji na stan gracza.',
            'description_short' => 'Opis wizualny, 3 warianty dialogu, wpływ na psychikę',
            'status' => 'done',
            'priority' => 'low',
            'created_by' => $leaderWriters->id,
        ]);
        $task4->assignees()->attach([$userBB->id]);

        $task5 = Task::create([
            'title' => 'Mechanika snów',
            'description' => 'Jaki jest warunek triggerowania snów, co bohater widzi, czy sny eskalują z czasem? Zaprojektować system progresji snów i ich wpływ na gameplay.',
            'description_short' => 'Warunek triggerowania, zawartość snów, eskalacja',
            'status' => 'done',
            'priority' => 'high',
            'created_by' => $admin->id,
        ]);

        $task6 = Task::create([
            'title' => 'Babcia z ciastem',
            'description' => 'Opis wizualny, motywacja wizyty, 3 warianty dialogu, jakie pozory sprawia, jaka się okaże? Postać powinna być wielowarstwowa i zaskakująca.',
            'description_short' => 'Opis wizualny, motywacja, warianty dialogu',
            'status' => 'done',
            'priority' => 'medium',
            'created_by' => $leaderWriters->id,
        ]);
        $task6->assignees()->attach([$userPB->id, $userOG->id]);

        $task7 = Task::create([
            'title' => 'Menu główne',
            'description' => 'Opcje menu, teksty przycisków, nastrój, background - przykładowy design, opis działania przycisków, dlaczego będzie niepokojące od startu? Zaprojektować unikalną atmosferę.',
            'description_short' => 'Opcje, teksty, nastrój, background',
            'status' => 'done',
            'priority' => 'high',
            'created_by' => $leaderGraphics->id,
        ]);
        $task7->assignees()->attach([$userBB->id]);

        $task8 = Task::create([
            'title' => 'Opis klatki schodowej',
            'description' => 'Opis klatki schodowej i bloku: dźwięki, zapachy sugerowane tekstem, detale które niepokoją, zmiany wprowadzane przez NPC dopasowane bezpośrednio pod konkretne postaci.',
            'description_short' => 'Dźwięki, zapachy, niepokojące detale, zmiany NPC',
            'status' => 'writers',
            'priority' => 'medium',
            'created_by' => $leaderWriters->id,
            'due_date' => now()->addDays(7),
        ]);
        $task8->assignees()->attach([$userBB->id]);

        $task9 = Task::create([
            'title' => 'Książę',
            'description' => 'Opis wizualny, motywacja wizyty, 3 warianty dialogu, jakie pozory sprawia, jaki się okaże? Postać musi być enigmatyczna i wieloznaczna.',
            'description_short' => 'Opis wizualny, motywacja, 3 warianty dialogu',
            'status' => 'writers',
            'priority' => 'high',
            'created_by' => $leaderWriters->id,
            'due_date' => now()->addDays(5),
        ]);
        $task9->assignees()->attach([$userPB->id]);

        $task10 = Task::create([
            'title' => 'Excel gameplay',
            'description' => 'Co gracz liczy/wpisuje, jak to wpływa na naszą postać i gracza przy dłuższej sesji, czy błędy wpływają na stan psychiczny bohatera? Zaprojektować mechanikę stresu.',
            'description_short' => 'Co gracz liczy, wpływ na postać, mechanika stresu',
            'status' => 'programmers',
            'priority' => 'high',
            'created_by' => $leaderProgrammers->id,
            'due_date' => now()->addDays(10),
        ]);
        $task10->assignees()->attach([$userBB->id]);

        $task11 = Task::create([
            'title' => 'System zapisywania gry',
            'description' => 'Zaprojektować system zapisywania stanu gry. Kiedy gra zapisuje postęp, jak obsługiwać wiele slotów zapisu, czy gracz może cofnąć decyzje?',
            'description_short' => 'Mechanika zapisu, sloty, cofanie decyzji',
            'status' => 'programmers',
            'priority' => 'medium',
            'created_by' => $leaderProgrammers->id,
            'due_date' => now()->addDays(14),
        ]);
        $task11->assignees()->attach([$userOG->id]);

        $task12 = Task::create([
            'title' => 'Interfejs inwentarza',
            'description' => 'Projekt UI dla inwentarza gracza. Jakie przedmioty może zbierać, jak są wyświetlane, czy mają opisy lore? Stworzyć mockup w spójnym stylu z resztą gry.',
            'description_short' => 'UI inwentarza, przedmioty, opisy lore',
            'status' => 'graphics',
            'priority' => 'medium',
            'created_by' => $leaderGraphics->id,
            'due_date' => now()->addDays(7),
        ]);
        $task12->assignees()->attach([$userBB->id]);

        // Sample comments
        Comment::create([
            'task_id' => $task1->id,
            'user_id' => $userPB->id,
            'content' => 'Rozpoczynam pracę nad opisami wizualnymi. Mam już kilka konceptów.',
        ]);

        Comment::create([
            'task_id' => $task1->id,
            'user_id' => $admin->id,
            'content' => 'Świetnie! Pamiętaj żeby każda postać miała przynajmniej jeden sygnał ostrzegawczy subtelny i jeden oczywisty.',
        ]);

        Comment::create([
            'task_id' => $task2->id,
            'user_id' => $userOG->id,
            'content' => 'Zaimplementowałam 3 poziomy trudności. Czwarty poziom aktywuje się automatycznie po osiągnięciu niskiej poczytalności.',
        ]);

        Comment::create([
            'task_id' => $task8->id,
            'user_id' => $leaderWriters->id,
            'content' => 'Proszę skupić się szczególnie na dźwiękach — to kluczowy element budowania nastroju w tej scenie.',
        ]);
    }
}
