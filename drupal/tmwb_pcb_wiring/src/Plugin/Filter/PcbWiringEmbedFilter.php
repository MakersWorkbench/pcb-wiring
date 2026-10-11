<?php

namespace Drupal\tmwb_pcb_wiring\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Embeds the PCB and Wiring Tools from a placeholder in content.
 *
 * Editors add a plain placeholder where the tool should appear:
 *
 * @code
 * <div class="tmwb-pcb-wiring"></div>
 * @endcode
 *
 * Optional attribute: data-heading="h2" (heading level of the tool's
 * title, default h2).
 *
 * This filter runs at render time. It swaps the placeholder for the tool's
 * mount point plus a short server-rendered description (the tool itself is
 * built by JavaScript, so crawlers need real text), attaches the library
 * that loads the tool, and optionally adds WebApplication structured data
 * (JSON-LD) to the page head. The tool mounts on a fixed element id, so a
 * page can hold only one instance.
 *
 * @Filter(
 *   id = "tmwb_pcb_wiring_embed",
 *   title = @Translation("TMWB PCB and Wiring Tools Embed"),
 *   description = @Translation("Replaces a div with the class tmwb-pcb-wiring with the PCB and Wiring Tools tool."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   settings = {
 *     "show_description" = TRUE,
 *     "structured_data" = TRUE,
 *   },
 *   weight = 10
 * )
 */
class PcbWiringEmbedFilter extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The request stack, used to build absolute URLs for the structured data.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The extension path resolver.
   *
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected $pathResolver;

  /**
   * Constructs the filter.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Extension\ExtensionPathResolver $path_resolver
   *   The extension path resolver.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RendererInterface $renderer, RequestStack $request_stack, ExtensionPathResolver $path_resolver) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->renderer = $renderer;
    $this->requestStack = $request_stack;
    $this->pathResolver = $path_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('renderer'),
      $container->get('request_stack'),
      $container->get('extension.path.resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['show_description'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show a description under the tool'),
      '#default_value' => $this->settings['show_description'],
      '#description' => $this->t('A short, server-rendered list of what the tool can decode. Helps search engines and visitors without JavaScript.'),
    ];
    $form['structured_data'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add structured data (JSON-LD)'),
      '#default_value' => $this->settings['structured_data'],
      '#description' => $this->t('Adds Schema.org WebApplication data to the page head so search engines understand what the tool is.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    if (strpos($text, 'tmwb-pcb-wiring') === FALSE) {
      return $result;
    }

    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);
    $nodes = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " tmwb-pcb-wiring ")]');

    if ($nodes->length === 0) {
      return $result;
    }

    // The tool mounts on a fixed element id, so only the first placeholder
    // on a page becomes the tool. Any further ones are removed instead of
    // producing duplicate ids.
    $replaced = FALSE;
    foreach ($nodes as $node) {
      if ($replaced) {
        $node->parentNode->removeChild($node);
        continue;
      }

      $heading = $node->getAttribute('data-heading');
      $render = [
        '#theme' => 'tmwb_pcb_wiring',
        '#heading' => preg_match('/^h[1-6]$/', $heading) ? $heading : 'h2',
        '#features' => static::features(),
        '#show_description' => !empty($this->settings['show_description']),
      ];
      $embed_html = (string) $this->renderer->renderInIsolation($render);

      // Parse the rendered markup into its own DOM, then import its nodes
      // in place of the placeholder.
      $embed_dom = Html::load($embed_html);
      $embed_body = $embed_dom->getElementsByTagName('body')->item(0);
      if ($embed_body) {
        foreach ($embed_body->childNodes as $child) {
          $node->parentNode->insertBefore($dom->importNode($child, TRUE), $node);
        }
      }
      $node->parentNode->removeChild($node);
      $replaced = TRUE;
    }

    $result->setProcessedText(Html::serialize($dom));
    $attachments = ['library' => ['tmwb_pcb_wiring/tools']];
    if (!empty($this->settings['structured_data'])) {
      $attachments['html_head'] = [[$this->structuredData(), 'tmwb_pcb_wiring_jsonld']];
      // The structured data holds the page's own URL.
      $result->addCacheContexts(['url.path']);
    }
    $result->addAttachments($attachments);

    return $result;
  }

  /**
   * The parts the tool can decode, as label and description pairs.
   *
   * Used both for the visible description under the tool and for the
   * structured data, so crawlers and visitors see the same list.
   *
   * @return array
   *   Array of [label, description] pairs.
   */
  public static function features(): array {
    return [
      ['Trace width', 'The copper trace width for a current, copper weight, and temperature rise, using IPC-2221, for outer and inner layers.'],
      ['Trace resistance', 'Resistance, voltage drop, and heat of a copper trace from its length, width, and copper weight.'],
      ['Via current', 'An estimate of the current a plated via can carry, and its resistance.'],
      ['Trace spacing', 'Minimum spacing between conductors for a voltage, from IPC-2221 Table 6-1.'],
      ['Wire gauge', 'AWG to mm squared conversion, and the voltage drop and power lost in a run of copper wire.'],
    ];
  }

  /**
   * Builds the WebApplication JSON-LD script for the page head.
   *
   * @return array
   *   A render array for an html_head script element.
   */
  protected function structuredData(): array {
    $request = $this->requestStack->getCurrentRequest();
    $origin = $request ? $request->getSchemeAndHttpHost() : '';
    $page_url = $request ? $origin . $request->getBaseUrl() . $request->getPathInfo() : '';
    $screenshot = $origin . ($request ? $request->getBasePath() : '') . '/' . $this->pathResolver->getPath('module', 'tmwb_pcb_wiring') . '/assets/images/pcb-overview.png';

    $publisher = [
      '@type' => 'Organization',
      'name' => 'TheMakersWorkbench.com',
      'url' => 'https://www.themakersworkbench.com',
    ];

    $data = [
      '@context' => 'https://schema.org',
      '@type' => 'WebApplication',
      'name' => 'PCB and Wiring Tools',
      'url' => $page_url,
      'description' => 'A free set of browser-based PCB and wiring tools: trace width for a current, trace resistance and voltage drop, via current, conductor spacing for a voltage, and wire gauge conversion with voltage drop.',
      'applicationCategory' => 'UtilitiesApplication',
      'applicationSubCategory' => 'PCB design calculators',
      'operatingSystem' => 'Any (web browser)',
      'browserRequirements' => 'Requires JavaScript',
      'inLanguage' => 'en',
      'isAccessibleForFree' => TRUE,
      'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
      'license' => 'https://www.apache.org/licenses/LICENSE-2.0',
      'author' => $publisher,
      'publisher' => $publisher,
      'screenshot' => ['@type' => 'ImageObject', 'url' => $screenshot, 'caption' => 'The PCB and Wiring Tools showing the trace width calculator'],
      'featureList' => array_map(function ($f) {
        return $f[0] . ': ' . $f[1];
      }, static::features()),
    ];

    return [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#attributes' => ['type' => 'application/ld+json'],
      '#value' => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('A div with the class tmwb-pcb-wiring is replaced by the PCB and Wiring Tools tool.');
  }

}
