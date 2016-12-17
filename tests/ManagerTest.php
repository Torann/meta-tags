<?php

namespace MetaTags\Test;

use Mockery;
use PHPUnit_Framework_TestCase;

class ContextTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldWebsiteTags()
    {
        $result = "<meta property=\"og:type\" content=\"website\">\n"
            . "<meta property=\"og:title\" content=\"Foo Bar\">\n"
            . "<meta property=\"twitter:title\" content=\"Foo Bar\">";

        $og = new \MetaTags\Manager();

        $og->title('Foo Bar');

        $this->assertEquals($result, $og->__toString());
    }

    /**
     * @test
     */
    public function shouldCreateTags()
    {
        $result = "<meta property=\"description\" content=\"A description\">\n"
            . "<meta property=\"og:type\" content=\"article\">\n"
            . "<meta property=\"og:title\" content=\"Foo Bar\">\n"
            . "<meta property=\"og:description\" content=\"A description\">\n"
            . "<meta property=\"og:site_name\" content=\"Foo Bar Website\">\n"
            . "<meta property=\"twitter:title\" content=\"Foo Bar\">\n"
            . "<meta property=\"twitter:description\" content=\"A description\">";

        $og = new \MetaTags\Manager();

        $og->title('Foo Bar')
            ->type('article')
            ->description('A description')
            ->siteName('Foo Bar Website');

        $this->assertEquals($result, $og->__toString());
    }

    /**
     * @test
     */
    public function shouldCreateTagsWithoutTwitter()
    {
        $result = "<meta property=\"description\" content=\"A description\">\n"
            . "<meta property=\"og:type\" content=\"article\">\n"
            . "<meta property=\"og:title\" content=\"Foo Bar\">\n"
            . "<meta property=\"og:description\" content=\"A description\">\n"
            . "<meta property=\"og:site_name\" content=\"Foo Bar Website\">";

        $og = new \MetaTags\Manager([
            'twitter' => false,
        ]);

        $og->title('Foo Bar')
            ->type('article')
            ->description('A description')
            ->siteName('Foo Bar Website');

        $this->assertEquals($result, $og->__toString());
    }

    /**
     * @test
     */
    public function shouldTruncateDescription()
    {
        $result = "<meta property=\"description\" content=\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nos...\">\n"
            . "<meta property=\"og:type\" content=\"website\">\n"
            . "<meta property=\"og:description\" content=\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi u...\">";

        $og = new \MetaTags\Manager([
            'twitter' => false,
        ]);

        $og->description('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.');

        $this->assertEquals($result, $og->__toString());
    }

    /**
     * @test
     */
    public function shouldSetUrl()
    {
        $result = "<meta property=\"og:type\" content=\"website\">\n"
            . "<meta property=\"og:url\" content=\"http://lyften.com\">";

        $og = new \MetaTags\Manager([
            'twitter' => false,
        ]);

        $og->url('http://lyften.com');

        $this->assertEquals($result, $og->__toString());
    }

    /**
     * @test
     */
    public function shouldAutomaticallySetUrl()
    {
        $result = "<meta property=\"og:type\" content=\"website\">\n"
            . "<meta property=\"og:url\" content=\"http://\">";

        $og = new \MetaTags\Manager([
            'twitter' => false,
        ]);

        $og->url();

        $this->assertEquals($result, $og->__toString());
    }

    /**
     * @test
     * @expectedException     \Exception
     */
    public function textTypeValidation()
    {
        $og = new \MetaTags\Manager([
            'validate' => true,
            'twitter' => false,
        ]);

        $og->type('mandy');

        $og->__toString();
    }

    /**
     * @test
     * @expectedException     \Exception
     */
    public function textContentValidation()
    {
        $og = new \MetaTags\Manager([
            'validate' => true,
            'twitter' => false,
        ]);

        $og->article([
            'foo' => 'bar',
        ]);

        $og->__toString();
    }

    /**
     * @test
     * @expectedException     \Exception
     */
    public function textUrlValidation()
    {
        $og = new \MetaTags\Manager([
            'validate' => true,
            'twitter' => false,
        ]);

        $og->url('Moo!');

        $og->__toString();
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
