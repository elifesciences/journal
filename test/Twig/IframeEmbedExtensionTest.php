<?php

namespace test\eLife\Journal\Twig;

use eLife\Journal\Twig\IframeEmbedExtension;
use PHPUnit\Framework\TestCase;
use Traversable;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\ArrayLoader;

final class IframeEmbedExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_twig_extension()
    {
        $extension = new IframeEmbedExtension();

        $this->assertInstanceOf(ExtensionInterface::class, $extension);
    }

    /**
     * @test
     * @depends it_is_a_twig_extension
     * @dataProvider htmlProvider
     */
    public function it_turns_embed_placeholders_into_embeds(string $input, string $expected)
    {
        $twigLoader = new ArrayLoader(['foo' => '{{ string|iframe_embed }}']);
        $twig = new Environment($twigLoader);
        $twig->addExtension(new IframeEmbedExtension());

        $this->assertSame($expected, $twig->render('foo', ['string' => $input]));
    }

    public function htmlProvider() : Traversable
    {
        yield 'no embed' => [
            '<p>No embed</p>',
            '<p>No embed</p>',
        ];
        yield 'mosaically in section' => [
            implode(PHP_EOL, [
                '<section class="article-section ">',
                '',
                '    <header class="article-section__header">',
                '      <h3 class="article-section__header_text">What is included with a Reviewed Preprint?</h3>',
                '    </header>',
                '',
                '  <div class="article-section__body">',
                '      <figure class="captioned-asset">',
                '',
                '    <picture class="captioned-asset__picture">',
                '        <source srcset="https://iiif.elifesciences.org/journal-cms/blog-article-preview%2F2022-10%2Fimage-20221020113037-1.png/full/1234,/0/default.webp 2x, https://iiif.elifesciences.org/journal-cms/blog-article-preview%2F2022-10%2Fimage-20221020113037-1.png/full/617,/0/default.webp 1x" type="image/webp">',
                '        <source srcset="https://iiif.elifesciences.org/journal-cms/blog-article-preview%2F2022-10%2Fimage-20221020113037-1.png/full/1234,/0/default.jpg 2x, https://iiif.elifesciences.org/journal-cms/blog-article-preview%2F2022-10%2Fimage-20221020113037-1.png/full/617,/0/default.jpg 1x" type="image/jpeg">',
                '        <img src="https://iiif.elifesciences.org/journal-cms/blog-article-preview%2F2022-10%2Fimage-20221020113037-1.png/full/617,/0/default.jpg" alt="" class="captioned-asset__image">',
                '    </picture>',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '</figure>',
                '<p class="paragraph"><a href="https://mosaically.com/embed/7ebca38f-d7ae-45bc-a8bf-0080f486314a">Embed IFrame</a><br>attribute-scrolling: no<br> attribute-width: 100%<br> attribute-height: 480<br> attribute-frameBorder: 0<br> attribute-allowfullscreen: null<br>Caption: Photo mosaic by: <a href="https://mosaically.com/elife">elife</a> @ <a href="https://mosaically.com">Mosaically</a></p>',
                '<p class="paragraph">A Reviewed Preprint, having undergone this process, will look very similar to an existing eLife research article, with the addition of:</p>',
                '',
                '',
                '    <ul class="list list--bullet">',
                '            <li><strong>More visible public reviews</strong> that describe the strengths and weaknesses of the work, and indicate whether the claims and conclusions are justified by the data. These public reviews will be available for authors and readers to access from the top of each article page.</li>',
                '            <li><strong>An eLife assessment </strong>that summarises the significance of the findings and the strength of the evidence reported in the preprint. Read more about <a href="https://elifesciences.org/inside-elife/db24dd46">eLife assessments</a>.</li>',
                '    </ul>',
                '',
                '<p class="paragraph">You can explore the following demo articles of Reviewed Preprints and the accompanying outputs below:</p>',
                '',
                '',
                '    <ul class="list list--bullet">',
                '            <li><a href="https://elifesciences.org/reviewed-preprints/81926">‘Hepatic lipid overload potentiates biliary epithelial cell activation via E2Fs’</a> (Yildiz et al.)</li>',
                '            <li><a href="https://elifesciences.org/reviewed-preprints/80494">‘Aging-related iron deposit prevents the benefits of HRT from late postmenopausal atherosclerosis’</a> (Xu et al.)</li>',
                '            <li><a href="https://elifesciences.org/reviewed-preprints/81535">\'Optogenetic induction of appetitive and aversive taste memories in <em>Drosophila</em>\'</a> (Jelen et al.)</li>',
                '    </ul>',
                '',
                '<p class="paragraph">Examples of Reviewed Preprints are <a href="https://elifesciences.org/reviewed-preprints">available here</a>.</p>',
                '',
                '',
                '',
                '',
                '',
                '  </div>',
                '',
                '</section>',
            ]),
            implode(PHP_EOL, [
                '<section class="article-section ">',
                '',
                '    <header class="article-section__header">',
                '      <h3 class="article-section__header_text">What is included with a Reviewed Preprint?</h3>',
                '    </header>',
                '',
                '  <div class="article-section__body">',
                '      <figure class="captioned-asset">',
                '',
                '    <picture class="captioned-asset__picture">',
                '        <source srcset="https://iiif.elifesciences.org/journal-cms/blog-article-preview%2F2022-10%2Fimage-20221020113037-1.png/full/1234,/0/default.webp 2x, https://iiif.elifesciences.org/journal-cms/blog-article-preview%2F2022-10%2Fimage-20221020113037-1.png/full/617,/0/default.webp 1x" type="image/webp">',
                '        <source srcset="https://iiif.elifesciences.org/journal-cms/blog-article-preview%2F2022-10%2Fimage-20221020113037-1.png/full/1234,/0/default.jpg 2x, https://iiif.elifesciences.org/journal-cms/blog-article-preview%2F2022-10%2Fimage-20221020113037-1.png/full/617,/0/default.jpg 1x" type="image/jpeg">',
                '        <img src="https://iiif.elifesciences.org/journal-cms/blog-article-preview%2F2022-10%2Fimage-20221020113037-1.png/full/617,/0/default.jpg" alt="" class="captioned-asset__image">',
                '    </picture>',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '</figure>',
                '<figure class="iframe-embed"><iframe src="https://mosaically.com/embed/7ebca38f-d7ae-45bc-a8bf-0080f486314a" scrolling="no" width="100%" height="480" frameBorder="0" allowfullscreen></iframe><small>Photo mosaic by: <a href="https://mosaically.com/elife">elife</a> @ <a href="https://mosaically.com">Mosaically</a></small></figure>',
                '<p class="paragraph">A Reviewed Preprint, having undergone this process, will look very similar to an existing eLife research article, with the addition of:</p>',
                '',
                '',
                '    <ul class="list list--bullet">',
                '            <li><strong>More visible public reviews</strong> that describe the strengths and weaknesses of the work, and indicate whether the claims and conclusions are justified by the data. These public reviews will be available for authors and readers to access from the top of each article page.</li>',
                '            <li><strong>An eLife assessment </strong>that summarises the significance of the findings and the strength of the evidence reported in the preprint. Read more about <a href="https://elifesciences.org/inside-elife/db24dd46">eLife assessments</a>.</li>',
                '    </ul>',
                '',
                '<p class="paragraph">You can explore the following demo articles of Reviewed Preprints and the accompanying outputs below:</p>',
                '',
                '',
                '    <ul class="list list--bullet">',
                '            <li><a href="https://elifesciences.org/reviewed-preprints/81926">‘Hepatic lipid overload potentiates biliary epithelial cell activation via E2Fs’</a> (Yildiz et al.)</li>',
                '            <li><a href="https://elifesciences.org/reviewed-preprints/80494">‘Aging-related iron deposit prevents the benefits of HRT from late postmenopausal atherosclerosis’</a> (Xu et al.)</li>',
                '            <li><a href="https://elifesciences.org/reviewed-preprints/81535">\'Optogenetic induction of appetitive and aversive taste memories in <em>Drosophila</em>\'</a> (Jelen et al.)</li>',
                '    </ul>',
                '',
                '<p class="paragraph">Examples of Reviewed Preprints are <a href="https://elifesciences.org/reviewed-preprints">available here</a>.</p>',
                '',
                '',
                '',
                '',
                '',
                '  </div>',
                '',
                '</section>',
            ]),
        ];
        yield 'mosaically no caption' => [
            '<p class="paragraph"><a href="https://mosaically.com/embed/7ebca38f-d7ae-45bc-a8bf-0080f486314a">Embed IFrame</a><br>attribute-scrolling: no<br> attribute-width: 100%<br> attribute-height: 480<br> attribute-frameBorder: 0<br> attribute-allowfullscreen: null</p>',
            '<figure class="iframe-embed"><iframe src="https://mosaically.com/embed/7ebca38f-d7ae-45bc-a8bf-0080f486314a" scrolling="no" width="100%" height="480" frameBorder="0" allowfullscreen></iframe></figure>',
        ];
        yield 'mosaically with caption ' => [
            '<p class="paragraph"><a href="https://mosaically.com/embed/7ebca38f-d7ae-45bc-a8bf-0080f486314a">Embed IFrame</a><br>attribute-scrolling: no<br> attribute-width: 100%<br> attribute-height: 480<br> attribute-frameBorder: 0<br> attribute-allowfullscreen: null<br>Caption: Photo mosaic by: <a href="https://mosaically.com/elife">elife</a> @ <a href="https://mosaically.com">Mosaically</a></p>',
            '<figure class="iframe-embed"><iframe src="https://mosaically.com/embed/7ebca38f-d7ae-45bc-a8bf-0080f486314a" scrolling="no" width="100%" height="480" frameBorder="0" allowfullscreen></iframe><small>Photo mosaic by: <a href="https://mosaically.com/elife">elife</a> @ <a href="https://mosaically.com">Mosaically</a></small></figure>',
        ];
        yield 'tiki-toki' => [
            '<p class="paragraph"><a href="https://www.tiki-toki.com/timeline/embed/1854566/1718310907/">Embed IFrame</a><br>attribute-onmousewheel: blank<br> attribute-frameborder: 0<br> attribute-scrolling: no<br> attribute-style: border-width: 0;<br> attribute-id: tl-timeline-iframe<br> attribute-width: 900<br> attribute-height: 480</p>',
            '<figure class="iframe-embed"><iframe src="https://www.tiki-toki.com/timeline/embed/1854566/1718310907/" onmousewheel="" frameborder="0" scrolling="no" style="border-width: 0;" id="tl-timeline-iframe" width="900" height="480"></iframe></figure>',
        ];
    }
}
