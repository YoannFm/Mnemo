<?php
namespace Database\Seeders;
use App\Models\User;
use App\Models\Module;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Utilisateur de démonstration
        $user = User::firstOrCreate(
            ['email' => 'demo@mnemo.fr'],
            [
                'name'              => 'Démo Rascol',
                'password'          => Hash::make('demo1234'),
                'email_verified_at' => now(),
            ]
        );

        // Module de démonstration public
        $module = $user->modules()->firstOrCreate(
            ['title' => 'Composants informatiques'],
            [
                'description' => 'Les principaux composants d\'un ordinateur : nom, rôle et équivalent anglais.',
                'is_public'   => true,
            ]
        );

        // Items du module
        $items = [
            ['Processeur', 'CPU', 'Effectue les calculs et exécute les instructions du système d\'exploitation et des logiciels.'],
            ['Mémoire vive', 'RAM', 'Stockage temporaire des données en cours d\'utilisation par le processeur.'],
            ['Carte graphique', 'GPU', 'Calcule et affiche les images, vidéos et effets 3D à l\'écran.'],
            ['Disque dur', 'HDD', 'Stockage permanent des données sur des plateaux magnétiques rotatifs.'],
            ['Disque SSD', 'SSD', 'Stockage permanent des données sur mémoire flash, plus rapide qu\'un HDD.'],
            ['Carte mère', 'Motherboard', 'Circuit imprimé principal qui relie tous les composants entre eux.'],
            ['Alimentation', 'PSU', 'Convertit le courant électrique alternatif en courant continu pour alimenter les composants.'],
            ['Ventirad', 'CPU Cooler', 'Système de refroidissement composé d\'un radiateur et d\'un ventilateur placé sur le processeur.'],
            ['Boîtier', 'Case', 'Enveloppe mécanique qui contient et protège tous les composants du PC.'],
            ['Carte réseau', 'NIC', 'Permet la connexion de l\'ordinateur à un réseau local ou Internet.'],
            ['Lecteur optique', 'Optical Drive', 'Lit et grave les disques CD, DVD et Blu-ray.'],
            ['Écran', 'Monitor', 'Périphérique de sortie qui affiche les informations visuelles générées par la carte graphique.'],
        ];

        foreach ($items as [$fr, $en, $func]) {
            $module->items()->firstOrCreate(
                ['name_fr' => $fr],
                ['name_en' => $en, 'function_text' => $func, 'photo_path' => null]
            );
        }
    }
}
