<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Module;

/**
 * Service de génération de questions pour le quiz.
 * Génère des QCM aléatoires selon différents types (Q1-Q16).
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
     * Types de questions supportés (Q1-Q16).
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
        'Q13' => [
            'field_question' => 'audio_path',
            'field_answer'   => 'name_fr',
            'question_text'  => 'Identification',
        ],
        'Q14' => [
            'field_question' => 'audio_path',
            'field_answer'   => 'name_alt',
            'question_text'  => 'Traduction',
        ],
        'Q15' => [
            'field_question' => 'name_fr',
            'field_answer'   => 'audio_path',
            'question_text'  => 'Prononciation',
        ],
        'Q16' => [
            'field_question' => 'name_alt',
            'field_answer'   => 'audio_path',
            'question_text'  => 'Prononciation',
        ],
    ];

    /**
     * Génère une question pour un module donné.
     *
     * @param Module $module Le module contenant les items
     * @param string $questionType Code du type (Q1-Q16)
     * @param Item|null $targetItem Item cible pré-sélectionné. Si null, tirage aléatoire.
     * @return array La question complète : énoncé, options mélangées, réponse correcte
     */
    public static function generateQuestion(Module $module, string $questionType = 'Q1', ?Item $targetItem = null, array $allowedTypes = [], int $optionCount = 4): array
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

        // Filtrer selon les champs actifs du module
        $eligibleTypes = self::filterTypesByModule($eligibleTypes, $module);

        if (empty($eligibleTypes)) {
            return ['error' => 'Aucun type de question compatible avec la configuration du module.'];
        }

        if (!in_array($questionType, $eligibleTypes)) {
            $candidates = empty($allowedTypes)
                ? $eligibleTypes
                : array_values(array_intersect($eligibleTypes, $allowedTypes));

            if (empty($candidates) && !empty($allowedTypes)) {
                // Aucun type du mode demandé n'est jouable avec cet item : chercher un
                // autre item du module qui, lui, permet de rester dans le mode choisi
                // plutôt que de basculer vers un type totalement différent.
                foreach ($allItems as $altItem) {
                    if ($altItem->id === $targetItem->id) {
                        continue;
                    }
                    $altEligible = self::filterTypesByModule(self::getEligibleTypes($allItems, $altItem), $module);
                    $altCandidates = array_values(array_intersect($altEligible, $allowedTypes));
                    if (!empty($altCandidates)) {
                        return self::generateQuestion($module, $altCandidates[array_rand($altCandidates)], $altItem, $allowedTypes, $optionCount);
                    }
                }

                return ['error' => 'Aucun item du module ne permet de générer ce type de question.'];
            }

            if (empty($candidates)) {
                $candidates = $eligibleTypes;
            }
            $questionType = $candidates[array_rand($candidates)];
        }

        $type = self::$questionTypes[$questionType];

        $optionCount = max(2, min(8, $optionCount));
        $needed = $optionCount - 1;

        // Distracteurs ayant une valeur non-vide pour field_answer
        $pool = $allItems
            ->reject(fn($item) => $item->id === $targetItem->id)
            ->filter(fn($item) => !empty($item->{$type['field_answer']}))
            ->shuffle();

        $wrongAnswers = $pool->take($needed)->pluck($type['field_answer'])->toArray();

        // Si pas assez de distracteurs, répéter des valeurs existantes
        if (count($wrongAnswers) < $needed && count($wrongAnswers) > 0) {
            while (count($wrongAnswers) < $needed) {
                $wrongAnswers[] = $wrongAnswers[array_rand($wrongAnswers)];
            }
        }

        // Affichage de la question
        if ($type['field_question'] === 'photo_path') {
            $questionContent = $targetItem->photo_url;
        } elseif ($type['field_question'] === 'audio_path') {
            $questionContent = asset('storage/' . $targetItem->audio_path);
        } else {
            $questionContent = $targetItem->{$type['field_question']};
        }

        // Réponse correcte + distracteurs
        $correctAnswer = $targetItem->{$type['field_answer']};

        $allAnswers = array_merge([$correctAnswer], $wrongAnswers);
        shuffle($allAnswers);

        // Convertir les chemins photo/audio en URLs
        if ($type['field_answer'] === 'photo_path') {
            $allAnswers    = array_map(fn($p) => asset('storage/' . $p), $allAnswers);
            $correctAnswer = asset('storage/' . $correctAnswer);
        } elseif ($type['field_answer'] === 'audio_path') {
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
     * - l'item cible a une valeur non-vide pour field_answer
     * - au moins 1 autre item a une valeur non-vide pour field_answer
     */
    public static function getEligibleTypes(\Illuminate\Support\Collection $allItems, Item $targetItem): array
    {
        $others = $allItems->reject(fn($i) => $i->id === $targetItem->id);

        return array_keys(array_filter(self::$questionTypes, function ($type) use ($targetItem, $others) {
            $fq = $type['field_question'];
            $fa = $type['field_answer'];
            $hasQuestion    = !empty($targetItem->{$fq});
            $hasAnswer      = !empty($targetItem->{$fa});
            $hasDistractors = $others->filter(fn($i) => !empty($i->{$fa}))->count() >= 1;
            return $hasQuestion && $hasAnswer && $hasDistractors;
        }));
    }

    /**
     * Filtre une liste de types de questions selon les champs activés sur le module
     * (photo, audio, fonction, nom FR, nom alternatif).
     */
    private static function filterTypesByModule(array $types, Module $module): array
    {
        return array_values(array_filter($types, function ($t) use ($module) {
            $photoTypes    = ['Q1', 'Q2', 'Q8'];
            $photoAnsTypes = ['Q3', 'Q9'];
            $audioTypes    = ['Q13', 'Q14'];
            $audioAnsTypes = ['Q15', 'Q16'];
            $funcTypes     = ['Q2', 'Q6', 'Q10', 'Q11'];
            $nameFrTypes   = ['Q1', 'Q4', 'Q6', 'Q7', 'Q9', 'Q10', 'Q13', 'Q15'];
            $nameAltTypes  = ['Q4', 'Q5', 'Q7', 'Q8', 'Q11', 'Q12', 'Q14', 'Q16'];
            if (in_array($t, $photoTypes) && !$module->field_photo) return false;
            if (in_array($t, $photoAnsTypes) && !$module->field_photo) return false;
            if (in_array($t, $audioTypes) && !$module->field_audio) return false;
            if (in_array($t, $audioAnsTypes) && !$module->field_audio) return false;
            if (in_array($t, $funcTypes) && !$module->field_function) return false;
            if (in_array($t, $nameFrTypes) && !$module->field_name_fr) return false;
            if (in_array($t, $nameAltTypes) && !$module->field_name_alt) return false;
            return true;
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
     * Retourne le champ d'entrée (field_question) pour un type donné.
     */
    public static function getFieldQuestion(string $questionType): ?string
    {
        return self::$questionTypes[$questionType]['field_question'] ?? null;
    }

    /**
     * Sélectionne un item cible qui possède le champ d'entrée requis par le type donné.
     * Garantit que le type de question sera éligible pour l'item sélectionné.
     */
    public static function pickTargetItem(Module $module, string $questionType): ?Item
    {
        if (!isset(self::$questionTypes[$questionType])) {
            return null;
        }
        $fieldQuestion = self::$questionTypes[$questionType]['field_question'];
        $eligible = $module->items()->get()->filter(fn($i) => !empty($i->{$fieldQuestion}));
        if ($eligible->isEmpty()) {
            return null;
        }
        return $eligible->random();
    }

    /**
     * Valide une réponse donnée pour une question.
     *
     * @param array $question La question générée
     * @param int|string $userAnswer L'indice de la réponse de l'utilisateur (0-7)
     * @return bool True si la réponse est correcte
     */
    public static function validateAnswer(array $question, $userAnswer): bool
    {
        $answerIndex = (int) $userAnswer;
        return $answerIndex === $question['correct_index'];
    }
}
