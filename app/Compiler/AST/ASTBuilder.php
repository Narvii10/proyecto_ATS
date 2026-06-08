<?php

namespace App\Compiler\AST;

use App\Compiler\Lexical\SymbolTable;
use App\Compiler\Lexical\Token;
use App\Compiler\Syntactic\ParseNode;

class ASTBuilder
{
    public function build(ParseNode $parseTree, SymbolTable $symbolTable): ASTNode
    {
        $root = new ASTNode('CVNode');

        $root->addChild($this->buildPersonalData($symbolTable));
        $root->addChild($this->buildEducation($symbolTable));
        $root->addChild($this->buildExperience($symbolTable));
        $root->addChild($this->buildSkills($symbolTable));

        return $root;
    }

    private function buildPersonalData(SymbolTable $st): ASTNode
    {
        $node = new ASTNode('PersonalDataNode');

        $name = $st->firstOfType(Token::TOKEN_NAME);
        if ($name) {
            $node->addChild(new ASTNode('NameNode', $name->value));
        }

        foreach ($st->byType(Token::TOKEN_EMAIL) as $token) {
            $node->addChild(new ASTNode('EmailNode', $token->value));
        }

        foreach ($st->byType(Token::TOKEN_PHONE) as $token) {
            $node->addChild(new ASTNode('PhoneNode', $token->value));
        }

        $age = $st->firstOfType(Token::TOKEN_AGE);
        if ($age) {
            $node->addChild(new ASTNode('AgeNode', (int) $age->value));
        }

        return $node;
    }

    private function buildEducation(SymbolTable $st): ASTNode
    {
        $node       = new ASTNode('EducationNode');
        $university = $st->firstOfType(Token::TOKEN_UNIVERSITY);
        $career     = $st->firstOfType(Token::TOKEN_CAREER);

        $degree = new ASTNode('DegreeNode');

        if ($university) {
            $degree->addChild(new ASTNode('UniversityNode', $university->value));
        }

        if ($career) {
            $degree->addChild(new ASTNode('CareerNode', $career->value));
        }

        $dates = $st->byType(Token::TOKEN_DATE);
        if (!empty($dates)) {
            $gradDate = end($dates);
            $degree->addChild(new ASTNode('GraduationDateNode', $gradDate->value));
        }

        if ($university || $career) {
            $node->addChild($degree);
        }

        return $node;
    }

    private function buildExperience(SymbolTable $st): ASTNode
    {
        $node     = new ASTNode('ExperienceNode');
        $dates    = $st->byType(Token::TOKEN_DATE);
        $expYears = $st->firstOfType(Token::TOKEN_EXPERIENCE_YEARS);

        // Sort tokens by document position so pairs are always chronological
        usort($dates, fn(Token $a, Token $b) =>
            $a->line !== $b->line ? $a->line - $b->line : $a->position - $b->position
        );

        // The last date is reserved for GraduationDateNode in buildEducation — exclude it
        if (count($dates) > 1) {
            array_pop($dates);
        }

        // Pair sorted dates as job periods
        $datePairs = array_chunk($dates, 2);
        foreach ($datePairs as $pair) {
            $job   = new ASTNode('JobNode');
            $attrs = [];

            if (isset($pair[0])) $attrs['start'] = $pair[0]->value;
            if (isset($pair[1])) $attrs['end']   = $pair[1]->value;

            if (!empty($attrs)) {
                // Swap if start is after end
                if (isset($attrs['start'], $attrs['end'])) {
                    $startInt = (int) preg_replace('/\D/', '', $attrs['start']);
                    $endInt   = (int) preg_replace('/\D/', '', $attrs['end']);
                    if ($startInt > $endInt) {
                        [$attrs['start'], $attrs['end']] = [$attrs['end'], $attrs['start']];
                    }
                }

                $attrs['months_duration'] = $this->calcMonths($attrs['start'] ?? null, $attrs['end'] ?? null);
                $period = new ASTNode('PeriodNode', null, $attrs);
                $job->addChild($period);
            }

            $node->addChild($job);
        }

        if ($expYears) {
            $node->attributes['stated_years'] = (int) $expYears->value;
        }

        return $node;
    }

    private function buildSkills(SymbolTable $st): ASTNode
    {
        $node = new ASTNode('SkillsNode');

        foreach ($st->byType(Token::TOKEN_SKILL) as $token) {
            $node->addChild(new ASTNode('TechSkillNode', $token->value));
        }

        foreach ($st->byType(Token::TOKEN_LANGUAGE) as $token) {
            $node->addChild(new ASTNode('LanguageNode', $token->value));
        }

        foreach ($st->byType(Token::TOKEN_CERTIFICATION) as $token) {
            $node->addChild(new ASTNode('CertificationNode', $token->value));
        }

        return $node;
    }

    private function calcMonths(?string $start, ?string $end): ?int
    {
        if (!$start || !$end) {
            return null;
        }
        try {
            $s = \Carbon\Carbon::parse(str_replace('/', '-', $start));
            $e = \Carbon\Carbon::parse(str_replace('/', '-', $end));
            return (int) abs($s->diffInMonths($e));
        } catch (\Exception) {
            return null;
        }
    }
}
