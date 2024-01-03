<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\NormalizeIndexBraceFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer;
use PhpCsFixer\Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer;
use PhpCsFixer\Fixer\Basic\EncodingFixer;
use PhpCsFixer\Fixer\Casing\ConstantCaseFixer;
use PhpCsFixer\Fixer\Casing\LowercaseKeywordsFixer;
use PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\CastNotation\LowercaseCastFixer;
use PhpCsFixer\Fixer\CastNotation\NoShortBoolCastFixer;
use PhpCsFixer\Fixer\CastNotation\ShortScalarCastFixer;
use PhpCsFixer\Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer;
use PhpCsFixer\Fixer\ClassNotation\NoUnneededFinalMethodFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer;
use PhpCsFixer\Fixer\Comment\NoTrailingWhitespaceInCommentFixer;
use PhpCsFixer\Fixer\ControlStructure\ElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\IncludeFixer;
use PhpCsFixer\Fixer\ControlStructure\NoBreakCommentFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer;
use PhpCsFixer\Fixer\ControlStructure\SwitchCaseSemicolonToColonFixer;
use PhpCsFixer\Fixer\ControlStructure\SwitchCaseSpaceFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FunctionNotation\FunctionDeclarationFixer;
use PhpCsFixer\Fixer\FunctionNotation\NoSpacesAfterFunctionNameFixer;
use PhpCsFixer\Fixer\FunctionNotation\ReturnTypeDeclarationFixer;
use PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\SingleImportPerStatementFixer;
use PhpCsFixer\Fixer\Import\SingleLineAfterImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareEqualNormalizeFixer;
use PhpCsFixer\Fixer\NamespaceNotation\BlankLineAfterNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\IncrementStyleFixer;
use PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\StandardizeIncrementFixer;
use PhpCsFixer\Fixer\Operator\StandardizeNotEqualsFixer;
use PhpCsFixer\Fixer\Operator\TernaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\FullOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\NoClosingTagFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitFqcnAnnotationFixer;
use PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAnnotationWithoutDotFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocIndentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoAccessFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoAliasTagFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoPackageFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoUselessInheritdocFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocReturnSelfReferenceFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocScalarFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSingleLineVarSpacingFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSummaryFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocToCommentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarWithoutNameFixer;
use PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer;
use PhpCsFixer\Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer;
use PhpCsFixer\Fixer\Semicolon\SpaceAfterSemicolonFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\IndentationTypeFixer;
use PhpCsFixer\Fixer\Whitespace\LineEndingFixer;
use PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer;
use PhpCsFixer\Fixer\Whitespace\NoTrailingWhitespaceFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use PhpCsFixer\Fixer\Whitespace\SingleBlankLineAtEofFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {

    $ecsConfig->paths([
        __DIR__.'/Controller',
        __DIR__.'/DependencyInjection',
        __DIR__.'/Generator',
        __DIR__.'/Resources',
        __DIR__.'/Type',
        __DIR__.'/Validator',
        __DIR__.'/GregwarCaptchaBundle.php',
    ]);
    
    $ecsConfig->rule(EncodingFixer::class);

    $ecsConfig->rule(FullOpeningTagFixer::class);

    $ecsConfig->rule(BlankLineAfterNamespaceFixer::class);

    $ecsConfig->rule(ElseifFixer::class);

    $ecsConfig->rule(FunctionDeclarationFixer::class);

    $ecsConfig->rule(IndentationTypeFixer::class);

    $ecsConfig->rule(LineEndingFixer::class);

    $ecsConfig->rule(ConstantCaseFixer::class);

    $ecsConfig->rule(LowercaseKeywordsFixer::class);

    $ecsConfig->rule(NoBreakCommentFixer::class);

    $ecsConfig->rule(NoClosingTagFixer::class);

    $ecsConfig->rule(NoSpacesAfterFunctionNameFixer::class);

    $ecsConfig->rule(NoTrailingWhitespaceFixer::class);

    $ecsConfig->rule(NoTrailingWhitespaceInCommentFixer::class);

    $ecsConfig->rule(SingleBlankLineAtEofFixer::class);

    $ecsConfig->rule(SingleImportPerStatementFixer::class);

    $ecsConfig->rule(SingleLineAfterImportsFixer::class);

    $ecsConfig->rule(SwitchCaseSemicolonToColonFixer::class);

    $ecsConfig->rule(SwitchCaseSpaceFixer::class);

    $ecsConfig->rule(VisibilityRequiredFixer::class);

    $ecsConfig->rule(BlankLineAfterOpeningTagFixer::class);

    $ecsConfig->rule(BinaryOperatorSpacesFixer::class);

    $ecsConfig->rule(IncrementStyleFixer::class);

    $ecsConfig->rule(UnaryOperatorSpacesFixer::class);

    $ecsConfig->rule(BlankLineBeforeStatementFixer::class);

    $ecsConfig->rule(CastSpacesFixer::class);

    $ecsConfig->rule(DeclareEqualNormalizeFixer::class);

    $ecsConfig->rule(IncludeFixer::class);

    $ecsConfig->rule(LowercaseCastFixer::class);

    $ecsConfig->rule(NativeFunctionCasingFixer::class);

    $ecsConfig->rule(NoBlankLinesAfterClassOpeningFixer::class);

    $ecsConfig->rule(NoBlankLinesAfterPhpdocFixer::class);

    $ecsConfig->rule(NoEmptyCommentFixer::class);

    $ecsConfig->rule(NoEmptyPhpdocFixer::class);

    $ecsConfig->rule(PhpdocSeparationFixer::class);

    $ecsConfig->rule(NoEmptyStatementFixer::class);

    $ecsConfig->rule(NoLeadingNamespaceWhitespaceFixer::class);

    $ecsConfig->rule(NoMultilineWhitespaceAroundDoubleArrowFixer::class);

    $ecsConfig->rule(NoShortBoolCastFixer::class);

    $ecsConfig->rule(NoSinglelineWhitespaceBeforeSemicolonsFixer::class);

    $ecsConfig->rule(NoSpacesAroundOffsetFixer::class);

    $ecsConfig->rule(NoUnneededControlParenthesesFixer::class);

    $ecsConfig->rule(NoWhitespaceBeforeCommaInArrayFixer::class);

    $ecsConfig->rule(NoWhitespaceInBlankLineFixer::class);

    $ecsConfig->rule(NormalizeIndexBraceFixer::class);

    $ecsConfig->rule(ObjectOperatorWithoutWhitespaceFixer::class);

    $ecsConfig->rule(PhpdocAnnotationWithoutDotFixer::class);

    $ecsConfig->rule(PhpdocIndentFixer::class);

    $ecsConfig->rule(PhpdocNoAccessFixer::class);

    $ecsConfig->rule(PhpdocNoEmptyReturnFixer::class);

    $ecsConfig->rule(PhpdocNoPackageFixer::class);

    $ecsConfig->rule(PhpdocNoUselessInheritdocFixer::class);

    $ecsConfig->rule(PhpdocReturnSelfReferenceFixer::class);

    $ecsConfig->rule(PhpdocScalarFixer::class);

    $ecsConfig->rule(PhpdocSingleLineVarSpacingFixer::class);

    $ecsConfig->rule(PhpdocSummaryFixer::class);

    $ecsConfig->rule(PhpdocToCommentFixer::class);

    $ecsConfig->rule(PhpdocTrimFixer::class);

    $ecsConfig->rule(PhpdocTypesFixer::class);

    $ecsConfig->rule(PhpdocVarWithoutNameFixer::class);

    $ecsConfig->rule(ReturnTypeDeclarationFixer::class);

    $ecsConfig->rule(SelfAccessorFixer::class);

    $ecsConfig->rule(ShortScalarCastFixer::class);

    $ecsConfig->rule(SingleQuoteFixer::class);

    $ecsConfig->rule(SpaceAfterSemicolonFixer::class);

    $ecsConfig->rule(StandardizeNotEqualsFixer::class);

    $ecsConfig->rule(TernaryOperatorSpacesFixer::class);

    $ecsConfig->rule(TrimArraySpacesFixer::class);

    $ecsConfig->rule(WhitespaceAfterCommaInArrayFixer::class);

    $ecsConfig->rule(MagicConstantCasingFixer::class);

    $ecsConfig->rule(NoLeadingImportSlashFixer::class);

    $ecsConfig->rule(NoUnusedImportsFixer::class);

    $ecsConfig->rule(PhpUnitFqcnAnnotationFixer::class);

    $ecsConfig->rule(PhpdocNoAliasTagFixer::class);

    $ecsConfig->rule(ProtectedToPrivateFixer::class);

    $ecsConfig->rule(NoUnneededFinalMethodFixer::class);

    $ecsConfig->rule(SemicolonAfterInstructionFixer::class);

    $ecsConfig->rule(YodaStyleFixer::class);

    $ecsConfig->rule(StandardizeIncrementFixer::class);
};
