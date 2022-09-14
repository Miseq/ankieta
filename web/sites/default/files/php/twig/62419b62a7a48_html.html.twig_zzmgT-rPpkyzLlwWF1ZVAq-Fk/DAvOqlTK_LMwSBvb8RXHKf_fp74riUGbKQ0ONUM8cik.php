<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* themes/pentacomp_uzp/templates/layout/html.html.twig */
class __TwigTemplate_99b1617874b2995d7d3083409693e86a extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 27
        $context["body_classes"] = [0 => ((        // line 28
($context["logged_in"] ?? null)) ? ("user-logged-in") : ("")), 1 => (( !        // line 29
($context["root_path"] ?? null)) ? ("path-frontpage") : (("path-" . \Drupal\Component\Utility\Html::getClass($this->sandbox->ensureToStringAllowed(($context["root_path"] ?? null), 29, $this->source))))), 2 => ((        // line 30
($context["node_type"] ?? null)) ? (("page-node-type-" . \Drupal\Component\Utility\Html::getClass($this->sandbox->ensureToStringAllowed(($context["node_type"] ?? null), 30, $this->source)))) : ("")), 3 => ((        // line 31
($context["db_offline"] ?? null)) ? ("db-offline") : (""))];
        // line 34
        echo "<!DOCTYPE html>
<html";
        // line 35
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["html_attributes"] ?? null), 35, $this->source), "html", null, true);
        echo ">
  <head>
    <head-placeholder token=\"";
        // line 37
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["placeholder_token"] ?? null), 37, $this->source), "html", null, true);
        echo "\">
    <title>";
        // line 38
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->safeJoin($this->env, $this->sandbox->ensureToStringAllowed(($context["head_title"] ?? null), 38, $this->source), " | "));
        echo "</title>
    <css-placeholder token=\"";
        // line 39
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["placeholder_token"] ?? null), 39, $this->source), "html", null, true);
        echo "\">
    <js-placeholder token=\"";
        // line 40
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["placeholder_token"] ?? null), 40, $this->source), "html", null, true);
        echo "\">

        <meta name='robots' content='max-image-preview:large' />
<link rel=\"alternate\" href=\"https://ezamowienia.gov.pl/pl/\" hreflang=\"pl\" />
<link rel=\"alternate\" href=\"https://ezamowienia.gov.pl/en/\" hreflang=\"en\" />
<link rel=\"alternate\" href=\"https://ezamowienia.gov.pl/\" hreflang=\"x-default\" />
<link rel='dns-prefetch' href='//s.w.org' />
<link rel='stylesheet' id='wp-block-library-css'  href='https://ezamowienia.gov.pl/wp-includes/css/dist/block-library/style.min.css?ver=5.8.4' type='text/css' media='all' />
<link rel='stylesheet' id='styles-css'  href='https://ezamowienia.gov.pl/wp-content/themes/portaldostepowy/dist/style.css?ver=5.6' type='text/css' media='all' />
<link rel=\"https://api.w.org/\" href=\"https://ezamowienia.gov.pl/wp-json/\" /><link rel=\"alternate\" type=\"application/json\" href=\"https://ezamowienia.gov.pl/wp-json/wp/v2/pages/5\" /><link rel=\"canonical\" href=\"https://ezamowienia.gov.pl/pl/\" />
<link rel=\"alternate\" type=\"application/json+oembed\" href=\"https://ezamowienia.gov.pl/wp-json/oembed/1.0/embed?url=https%3A%2F%2Fezamowienia.gov.pl%2Fpl%2F\" />
<link rel=\"alternate\" type=\"text/xml+oembed\" href=\"https://ezamowienia.gov.pl/wp-json/oembed/1.0/embed?url=https%3A%2F%2Fezamowienia.gov.pl%2Fpl%2F&#038;format=xml\" />
<script type=\"text/javascript\">var faqEndpoint = \"https://ezamowienia.gov.pl/soz/api/faq/latest\";</script>        

  </head>
  <body";
        // line 55
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [0 => ($context["body_classes"] ?? null)], "method", false, false, true, 55), 55, $this->source), "html", null, true);
        echo ">
    ";
        // line 60
        echo "    <a href=\"#main-content\" class=\"visually-hidden focusable skip-link\">
      ";
        // line 61
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Skip to main content"));
        echo "
    </a>
    ";
        // line 63
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["page_top"] ?? null), 63, $this->source), "html", null, true);
        echo "
    ";
        // line 64
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["page"] ?? null), 64, $this->source), "html", null, true);
        echo "
    ";
        // line 65
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["page_bottom"] ?? null), 65, $this->source), "html", null, true);
        echo "
    <js-bottom-placeholder token=\"";
        // line 66
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["placeholder_token"] ?? null), 66, $this->source), "html", null, true);
        echo "\">
  </body>
</html>
";
    }

    public function getTemplateName()
    {
        return "themes/pentacomp_uzp/templates/layout/html.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  107 => 66,  103 => 65,  99 => 64,  95 => 63,  90 => 61,  87 => 60,  83 => 55,  65 => 40,  61 => 39,  57 => 38,  53 => 37,  48 => 35,  45 => 34,  43 => 31,  42 => 30,  41 => 29,  40 => 28,  39 => 27,);
    }

    public function getSourceContext()
    {
        return new Source("", "themes/pentacomp_uzp/templates/layout/html.html.twig", "/var/www/html/web/themes/pentacomp_uzp/templates/layout/html.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("set" => 27);
        static $filters = array("clean_class" => 29, "escape" => 35, "safe_join" => 38, "t" => 61);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                ['set'],
                ['clean_class', 'escape', 'safe_join', 't'],
                []
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
