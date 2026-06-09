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
 * - field_answer : champ à trouver (ex : name_fr, name_alt)
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
            'question_text'  => 'Identification',
        ],
        'Q2' => [
            'field_question' => 'photo_path',
            'field_answer'   => 'function_text',
            'question_text'  => 'Description',
        ],
        'Q3' => [
            'field_question' => 'function_text',
            'field_answer'   => 'photo_path',
            'question_text'  => 'Reconnaissance',
        ],
        'Q4' => [
            'field_question' => 'name_fr',
            'field_answer'   => 'name_alt',
            'question_text'  => 'Traduction',
        ],
        'Q5' => [
            'field_question' => 'name_alt',
            'field_answer'   => 'photo_path',
            'question_text'  => 'Correspondance',
        ],
        'Q6' => [
            'field_question' => 'function_text',
            'field_answer'   => 'name_fr',
            'question_text'  => 'Désignation',
        ],
        'Q7' => [
            'field_question' => 'name_alt',
            'field_answer'   => 'name_fr',
            'question_text'  => 'Traduction',
        ],
        'Q8' => [
            'field_question' => 'photo_path',
            'field_answer'   => 'name_alt',
            'question_text'  => 'Traduction',
        ],
        'Q9' => [
            'field_question' => 'name_fr',
            'field_answer'   => 'photo_path',
            'question_text'  => 'Visualisation',
        ],
        'Q10' => [
            'field_question' => 'name_fr',
            'field_answer'   => 'function_text',
            'question_text'  => 'Définition',
        ],
        'Q11' => [
            'field_question' => 'name_alt',
            'field_answer'   => 'function_text',
            'question_text'  => 'Signification',
        ],
        'Q12' => [
            'field_question' => 'function_text',
            'field_answer'   => 'name_alt',
            'question_text'  => 'Traduction',
        ],
    ];

    /**
     * Génère une question pour un module donné.
     *
     * @param Module $module Le module contenant les items
     * @param string $questionType Code du type (Q1-Q11)
     * @param Item|null $targetItem Item cible pré-sélectionné. Si null, tirage aléatoire.
     * @return array La question complète : énoncé, options mélangées, réponse correcte
     */
    public static function generateQuestion(Module $module, string $questionType = 'Q1', ?Item $targetItem = null): array
    {
        $allItems = $module->items()->get();

        if ($allItems->isEmpty()) {
            return ['error' => 'Aucun item dans ce module.'];
        }

        $targetItem = $targetItem ?? $allItems->random();

        // Si le type demandé n'est pas jouable avec cet item, chercher un type compatible
        $eligibleTypes = self::getEligibleTypes($allItems, $targetItem);

        if (empty($eligibleTypes)) {
            return ['error' => 'Cet item n\'a pas assez de données pour générer une question.'];
        }

        if (!in_array($questionType, $eligibleTypes)) {
            $questionType = $eligibleTypes[array_rand($eligibleTypes)];
        }

        $type = self::$questionTypes[$questionType];

        // 3 distracteurs ayant une valeur non-vide pour field_answer
        $distractors = $allItems
            ->reject(fn($item) => $item->id === $targetItem->id)
            ->filter(fn($item) => !empty($item->{$type['field_answer']}))
            ->shuffle()
            ->take(3);

        // Affichage de la question
        $questionContent = $type['field_question'] === 'photo_path'
            ? $targetItem->photo_url
            : $targetItem->{$type['field_question']};

        // Réponse correcte + distracteurs
        $correctAnswer = $targetItem->{$type['field_answer']};
        $wrongAnswers  = $distractors->pluck($type['field_answer'])->toArray();

        $allAnswers = array_merge([$correctAnswer], $wrongAnswers);
        shuffle($allAnswers);

        // Convertir les chemins photo en URLs
        if ($type['field_answer'] === 'photo_path') {
            $allAnswers    = array_map(fn($p) => asset('storage/' . $p), $allAnswers);
            $correctAnswer = asset('storage/' . $correctAnswer);
        }

        $correctIndex = array_search($correctAnswer, $allAnswers, true);

        return [
            'item_id'          => $targetItem->id,
            'question_text'    => $type['question_text'],
            'question_type'    => $questionType,
            'field_question'   => $type['field_question'],
            'field_answer'     => $type['field_answer'],
            'question_content' => $questionContent,
            'options'          => $allAnswers,
            'correct_answer'   => $correctAnswer,
            'correct_index'    => $correctIndex,
        ];
    }

    /**
     * Retourne les types de questions jouables pour un item donné dans un module.
     * Un type est éligible si :
     * - l'item cible a une valeur non-vide pour field_question
     * - au moins 1 autre item a une valeur non-vide pour field_answer
     */
    public static function getEligibleTypes(\Illuminate\Support\Collection $allItems, Item $targetItem): array
    {
        $others = $allItems->reject(fn($i) => $i->id === $targetItem->id);

        return array_keys(array_filter(self::$questionTypes, function ($type) use ($targetItem, $others) {
            $hasQuestion    = !empty($targetItem->{$type['field_question']});
            $hasDistractors = $others->filter(fn($i) => !empty($i->{$type['field_answer']}))->count() >= 1;
            return $hasQuestion && $hasDistractors;
        }));
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
