<?php
namespace App\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class Cast extends FunctionNode
{
    private $expression;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER); // CAST
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->expression = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_AS);
        $parser->match(TokenType::T_IDENTIFIER); // INTEGER یا هر نوع دیگه
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'CAST(' . $sqlWalker->walkArithmeticPrimary($this->expression) . ' AS SIGNED)';
    }
}