<?php
namespace App\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class Cast extends FunctionNode
{
    private $expression;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER); // CAST
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->expression = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_AS);
        $parser->match(Lexer::T_IDENTIFIER); // INTEGER یا هر نوع دیگه
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        // به جای استفاده از $this->type، مستقیماً SIGNED رو می‌ذاریم
        return 'CAST(' . $sqlWalker->walkArithmeticPrimary($this->expression) . ' AS SIGNED)';
    }
}