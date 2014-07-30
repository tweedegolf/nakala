<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Behat context class.
 */
class CoreContext implements SnippetAcceptingContext
{
    private $phpword;

    private $section;

    private $source;

    private $output;

    protected function getFilename($file)
    {
        return __DIR__ . '/../../resources/' . $file;
    }

    /**
     * Initializes context.
     *
     * Every scenario gets its own context object.
     * You can also pass arbitrary arguments to the context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @Given the document :document is provided
     */
    public function theDocumentIsProvided($document)
    {
        $this->source = file_get_contents($this->getFilename($document));
    }

    /**
     * @When I convert the document
     */
    public function iConvertTheDocument()
    {
        $this->phpword = new \PhpOffice\PhpWord\PhpWord();
        $this->section = $this->phpword->addSection();

        $this->phpword->addTitleStyle(0, [
            'size' =>  30,
            'bold' => true,
        ]);

        $this->phpword->addTitleStyle(1, [
            'size' => 20,
        ]);

        $this->phpword->addTitleStyle(2, [
            'size' => 18,
            'bold' => true,
        ]);

        $this->phpword->addTitleStyle(3, [
            'size' => 16,
            'bold' => true,
        ]);

        $this->phpword->addTitleStyle(4, [
            'size' => 14,
            'bold' => true,
        ]);

        $this->phpword->addTitleStyle(5, [
            'size' => 14,
        ]);

        $this->phpword->setDefaultFontName('Times new Roman');

        $dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
        $converter = new \Tweedegolf\Nakala\Converter($dispatcher);

        $wordHandler = new \Tweedegolf\Nakala\Handler\Word($dispatcher);
        $wordHandler->setDocumentSection($this->section);

        $converter->addHandler($wordHandler);

        $reader = new \Tweedegolf\Nakala\Reader\Html();
        $reader->setContent($this->source);
        $converter->convert($reader);
        $this->output = $wordHandler->getOutput();
    }

    /**
     * @Then it should be the same as :document
     */
    public function itShouldBeTheSameAs($document)
    {
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($this->phpword, 'Word2007');
        $writer->save($this->getFilename($document));
        throw new PendingException();
    }
}
