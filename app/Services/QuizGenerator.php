<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Module;

/**
 * Service de génération de questions pour le quiz.
 * Génère des QCM aléatoires selon différents types (Q1-Q4).
 *
 * Structure générique :
 * - field_question : champ affiché (ex : photo_path, function_text)
 * - field_answer : champ à trouver (ex : name_fr, name_en)
 * - question_text : texte descriptif du type de question
 *
 * Chaque question = 1 item cible + 3 distracteurs tirés du même module.
 */
class QuizGenerator
{
    /**
     * Types de questions supportés (Q1-Q8).
     * Chaque type définit :
     *   - field_question : ce qui est affiché à l'utilisateur
     *   - field_answer : ce que l'utilisateur doit trouver
     *   - question_text : énoncé affiché
     */
    private static array $questionTypes = [
        'Q1' => [
            'field_question' => 'photo_path',
            'field_answer'   => 'name_fr',
            'question_text'  => 'Quel est le nom français de cet élément ?',
        ],
        'Q2' => [
            'field_question' => 'photo_path',
            'field_answer'   => 'function_text',
            'question_text'  => 'Quelle est la fonction de cet élément ?',
        ],
        'Q3' => [
            'field_question' => 'function_text',
            'field_answer'   => 'photo_path',
            'question_text'  => 'Quelle photo correspond à cette description ?',
        ],
        'Q4' => [
            'field_question' => 'name_fr',
            'field_answer'   => 'name_en',
            'question_text'  => 'Quel est le nom anglais ?',
        ],
        'Q5' => [
            'field_question' => 'name_en',
            'field_answer'   => 'photo_path',
            'question_text'  => 'Quelle photo correspond à ce nom anglais ?',
        ],
        'Q6' => [
            'field_question' => 'function_text',
            'field_answer'   => 'name_fr',
            'question_text'  => 'Quel est le nom français correspondant à cette fonction ?',
        ],
        'Q7' => [
            'field_question' => 'name_en',
            'field_answer'   => 'name_fr',
            'question_text'  => 'Quel est le nom français de ce terme anglais ?',
        ],
        'Q8' => [
            'field_question' => 'photo_path',
            'field_answer'   => 'name_en',
            'question_text'  => 'Quel est le nom anglais de cet élément ?',
        ],
    ];

    /**
     * Génère une question pour un module donné.
     *
     * @param Module $module Le module contenant les items
     * @param string $questionType Code du type (Q1-Q8)
     * @param Item|null $targetItem Item cible pré-sélectionné (ex : tirage pondéré par l'appelant). Si null, tirage aléatoire.
     * @return array La question complète : énoncé, options mélangées, réponse correcte
     */
    public static function generateQuestion(Module $module, string $questionType = 'Q1', ?Item $targetItem = null): array
    {
        // Récupérer le type demandé, sinon Q1 par défaut
        $type = self::$questionTypes[$questionType] ?? self::$questionTypes['Q1'];

        // Récupérer tous les items du module
        $allItems = $module->items()->get();

        if ($allItems->isEmpty()) {
            return [
                'error' => 'Aucun item dans ce module.',
            ];
        }

        // Utiliser l'item pré-sélectionné (ex : tirage pondéré) ou tirer aléatoirement
        $targetItem = $targetItem ?? $allItems->random();

        // Récupérer 3 distracteurs (items différents du cible)
        $distractors = $allItems
            ->reject(fn ($item) => $item->id === $targetItem->id)
            ->random(min(3, $allItems->count() - 1)); // Au moins 1 item, max 3 distracteurs

        // Affichage de la question
        $questionContent = $targetItem->{$type['field_question']};

        // Si c'est une photo, on affiche l'URL
        if ($type['field_question'] === 'photo_path') {
            $questionContent = $targetItem->photo_url;
        }

        // Réponse correcte
        $correctAnswer = $targetItem->{$type['field_answer']};

        // Réponses distracteurs
        $wrongAnswers = $distractors->pluck($type['field_answer'])->toArray();

        // Mélanger toutes les réponses (correct + distracteurs)
        $allAnswers = array_merge([$correctAnswer], $wrongAnswers);
        shuffle($allAnswers);

        // Si les réponses sont des photos, les convertir en URLs complètes
        if ($type['field_answer'] === 'photo_path') {
            $allAnswers = array_map(fn($path) => asset('storage/' . $path), $allAnswers);
            $correctAnswer = asset('storage/' . $correctAnswer);
        }

        // Trouver l'indice de la bonne réponse après mélange
        $correctIndex = array_search($correctAnswer, $allAnswers, true);

        return [
            'item_id'         => $targetItem->id,
            'question_text'   => $type['question_text'],
            'question_type'   => $questionType,
            'field_question'  => $type['field_question'], // Ce qui est affiché (photo ou texte)
            'field_answer'    => $type['field_answer'], // Ce qu'il faut répondre
            'question_content'=> $questionContent, // URL de photo ou texte
            'options'         => $allAnswers,
            'correct_answer'  => $correctAnswer,
            'correct_index'   => $correctIndex, // Indice de la bonne réponse (0-2)
        ];
    }

    /**
     * Retourne la liste de tous les types de questions supportés.
     */
    public static function getQuestionTypes(): array
    {
        return array_keys(self::$questionTypes);
    }

    /**
     * Valide une réponse donnée pour une question.
     *
     * @param array $question La question générée
     * @param int|string $userAnswer L'indice de la réponse de l'utilisateur (0-2)
     * @return bool True si la réponse est correcte
     */
    public static function validateAnswer(array $question, $userAnswer): bool
    {
        $answerIndex = (int) $userAnswer;
        return $answerIndex === $question['correct_index'];
    }
}
