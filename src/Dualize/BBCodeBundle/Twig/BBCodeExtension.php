<?php

namespace Dualize\BBCodeBundle\Twig;

use Symfony\Component\DependencyInjection\Container;

/**
 * Description of BBCodeExtension
 */
class BBCodeExtension extends \Twig_Extension
{

    public $tags = ['b', 'i', 'u', 's', 'center', 'size', 'color', 'img', 'video', 'link', 'url', 'quote', 'smile'];
    public $buttons = ['b', 'i', 'u', 's', 'center', 'size', 'color', 'img', 'url', 'quote', 'smile', 'preview'];
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('bbcode_panel', array($this, 'bbcodePanelFunction'), array(
                'is_safe' => array('html'),
                    )
            )
        );
    }

    public function bbcodePanelFunction($buttons = [])
    {
        return $this->container->get('templating')->render('DualizeBBCodeBundle:BBCode:bbcode_panel.html.twig', [
                    'btn' => count($buttons) == 0 ? $this->buttons : $buttons,
        ]);
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('dualize_bbcode', array($this, 'bbcodeFilter'), array(
                'is_safe' => array('html'),
                    )
            ),
            new \Twig_SimpleFilter('dualize_bbcode_excerpt', array($this, 'bbcodeExcerptFilter')),
        );
    }

    public function bbcodeFilter($string, $allowed_tags = [])
    {
        if (count($allowed_tags) == 0) {
            $allowed_tags = $this->tags;
        }

        $BBCodes = array(
            'b' => array(
                'type' => BBCODE_TYPE_NOARG,
                'open_tag' => '<b>',
                'close_tag' => '</b>',
                'childs' => 'b,i,u,s,size,color,url',
            ),
            'i' => array(
                'type' => BBCODE_TYPE_NOARG,
                'open_tag' => '<i>',
                'close_tag' => '</i>',
                'childs' => 'b,i,u,s,size,color,url',
            ),
            'u' => array(
                'type' => BBCODE_TYPE_NOARG,
                'open_tag' => '<u>',
                'close_tag' => '</u>',
                'childs' => 'b,i,u,s,size,color,url',
            ),
            's' => array(
                'type' => BBCODE_TYPE_NOARG,
                'open_tag' => '<s>',
                'close_tag' => '</s>',
                'childs' => 'b,i,u,s,size,color,url',
            ),
            'center' => array(
                'type' => BBCODE_TYPE_NOARG,
                'open_tag' => '<div class="bbcode-center">',
                'close_tag' => '</div>',
                'childs' => 'b,i,u,s,center,size,color,url,quote',
            ),
            'size' => array(
                'type' => BBCODE_TYPE_ARG,
                'open_tag' => '<span class="bbcode-size-{PARAM}">',
                'close_tag' => '</span>',
                'childs' => 'b,i,u,s,size,color,url',
            ),
            'color' => array(
                'type' => BBCODE_TYPE_ARG,
                'open_tag' => '<span style="color: {PARAM};">',
                'close_tag' => '</span>',
                'childs' => 'b,i,u,s,size,color,url',
            ),
            'url' => array(
                'type' => BBCODE_TYPE_OPTARG,
                'open_tag' => '<a href="{PARAM}" target="_blank">',
                'close_tag' => '</a>',
                'default_arg' => '{CONTENT}',
                'childs' => 'b,i,u,s,size,color,url',
            ),
            'quote' => array(
                'type' => BBCODE_TYPE_OPTARG,
                'open_tag' => '<div class="bbcode-quote">{PARAM}',
                'close_tag' => '</div>',
                'childs' => 'b,i,u,s,center,size,color,url,quote',
                'param_handling' => __NAMESPACE__ . '\BBCodeExtension::quoteParam',
            ),
        );

        // Filter bbcodes
        $allowed_bbcodes = [];
        foreach ($allowed_tags as $tag) {
            if (isset($BBCodes[$tag])) {
                $allowed_bbcodes[$tag] = $BBCodes[$tag];
            }
        }

        $BBHandler = bbcode_create($allowed_bbcodes);

        // Text filters
        $markup = trim($string);
        $markup = htmlspecialchars($markup);
        $markup = bbcode_parse($BBHandler, $markup);

        // Smileys
        $smileys = array(
            ':)' => 'smile',
            ':(' => 'sad',
            ':D' => 'laugh',
            ':O' => 'shock',
            ':P' => 'playful',
            ':/' => 'skeptical',
            ':|' => 'straight',
        );

        foreach ($smileys as $k => $v) {
            $markup = preg_replace('~(^|\s)' . preg_quote($k) . '($|\s)~', '$1<span class="bbcode-smileys-' . $v . '"></span>$2', $markup);
        }

        // Media
        if (in_array('video', $allowed_tags)) {
            $markup = $this->youtubeInsertion($markup);
        }

        if (in_array('img', $allowed_tags)) {
            $markup = $this->imgInsertion($markup);
        }

        if (in_array('link', $allowed_tags)) {
            $markup = $this->linkInsertion($markup);
        }

        $markup = preg_replace('/[\r\n]{3,}/', "\n\n", $markup); // remove multiple empty lines
        $markup = nl2br($markup);
        $markup = str_replace('</div><br />', '</div>', $markup); // remove empty lines after divs

        return $markup;
    }

    public static function quoteParam($content, $argument)
    {
        if ($argument) {
            return '<div class="bbcode-quote-author">' . $argument . '</div>';
        }
    }

    public function linkInsertion($markup)
    {
        $link = '%s<a href="%s" target="_blank">%s</a>%s';

        $markup = preg_replace_callback('~
        (^|\s|>)                              # Word border
        (
        https?://                             # Required scheme. Either http or https.
        (?:[\w-\.]+\.[\w]{2,10})              # Domain
        [^$\s<]*                              # Path
        )
        ($|\s|<[^(?:/a>)])                    # Word border
        ~ixm', function($m) use ($link) {
            $link_text = mb_strlen($m[2]) > 80 ? substr($m[2], 0, 80) . '...' : $m[2];
            return sprintf($link, $m[1], $m[2], $link_text, $m[3]);
        }, $markup);

        return $markup;
    }

    public function imgInsertion($markup)
    {
        $img_embed = '%s<img src="%s">%s';

        $markup = preg_replace('~
        (^|\s|>)                               # Word border
        (
        https?://                              # Required scheme. Either http or https.
        (?:[\w-\.]+\.[\w]{2,10})               # Domain
        (?:/\S+)                               # Path
        \.(?:jpe?g|png|gif)                    # Image extension
        )
        ($|\s|<)                               # Word border
        ~ixm', sprintf($img_embed, '$1', '$2', '$3'), $markup);

        return $markup;
    }

    function youtubeInsertion($markup)
    {
        $youtube_embed = '%s<iframe width="640" height="360" src="//www.youtube.com/embed/%s?feature=player_detailpage" frameborder="0" allowfullscreen></iframe>%s';

        // link - http://stackoverflow.com/questions/5830387/how-to-find-all-youtube-video-ids-in-a-string-using-a-regex
        // added word borders and multiline mode

        $markup = preg_replace('~
        (^|\s|>)          # Word border
        # Match non-linked youtube URL in the wild. (Rev:20130823)
        https?://         # Required scheme. Either http or https.
        (?:[0-9A-Z-]+\.)? # Optional subdomain.
        (?:               # Group host alternatives.
          youtu\.be/      # Either youtu.be,
        | youtube         # or youtube.com or
          (?:-nocookie)?  # youtube-nocookie.com
          \.com           # followed by
          \S*             # Allow anything up to VIDEO_ID,
          [^\w\s-]       # but char before ID is non-ID char.
        )                 # End host alternatives.
        ([\w-]{11})      # $1: VIDEO_ID is exactly 11 chars.
        (?=[^\w-]|$)     # Assert next char is non-ID or EOS.
        (?!               # Assert URL is not pre-linked.
          [?=&+%\w.-]*    # Allow URL (query) remainder.
          (?:             # Group pre-linked alternatives.
            [\'"][^<>]*>  # Either inside a start tag,
          | </a>          # or inside <a> element text contents.
          )               # End recognized pre-linked alts.
        )                 # End negative lookahead assertion.
        [?=&+%\w.-]*      # Consume any URL (query) remainder.
        ($|\s|<)          # Word border
        ~ixm', sprintf($youtube_embed, '$1', '$2', '$3'), $markup);

        return $markup;
    }

    public function bbcodeExcerptFilter($string, $length)
    {
        if (!$length) {
            $length = 250;
        }

        // Remove quotes with content
        $string = preg_replace('/\[quote(?:=.+?)?\].*?\[\/quote\]/ixms', '', $string);

        // Remove bbtags
        $string = preg_replace('/\[([\w]+?)(?:=[#\w]+?)?\](.*?)\[\/\1\]/ixms', '$2', $string);

        // Truncate string by end of word
        if (mb_strlen($string) > $length) {
            $string = mb_substr($string, 0, $length);
            $string = mb_substr($string, 0, mb_strrpos($string, ' '));
            $string = $string . ' ...';
        }

        return $string;
    }

    public function getName()
    {
        return 'parse_bbcode';
    }

}
