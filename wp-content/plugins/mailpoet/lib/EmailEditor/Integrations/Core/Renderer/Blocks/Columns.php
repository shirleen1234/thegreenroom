<?php declare(strict_types = 1);

namespace MailPoet\EmailEditor\Integrations\Core\Renderer\Blocks;

if (!defined('ABSPATH')) exit;


use MailPoet\EmailEditor\Engine\SettingsController;
use MailPoet\EmailEditor\Integrations\Core\Renderer\Blocks\AbstractBlockRenderer;
use MailPoet\EmailEditor\Integrations\Utils\DomDocumentHelper;
use WP_Style_Engine;

class Columns extends AbstractBlockRenderer {
  public function render(string $blockContent, array $parsedBlock, SettingsController $settingsController): string {
    $content = '';
    foreach ($parsedBlock['innerBlocks'] ?? [] as $block) {
      $content .= render_block($block);
    }

    return str_replace(
      '{columns_content}',
      $content,
      $this->getBlockWrapper($blockContent, $parsedBlock, $settingsController)
    );
  }

  /**
   * Based on MJML <mj-section>
   */
  private function getBlockWrapper(string $blockContent, array $parsedBlock, SettingsController $settingsController): string {
    $originalWrapperClassname = (new DomDocumentHelper($blockContent))->getAttributeValueByTagName('div', 'class') ?? '';
    $block_attributes = wp_parse_args($parsedBlock['attrs'] ?? [], [
      'align' => null,
      'width' => $settingsController->getLayoutWidthWithoutPadding(),
      'style' => [],
    ]);

    $cellStyles = $this->getStylesFromBlock([
      'spacing' => [ 'padding' => $block_attributes['style']['spacing']['padding'] ?? [] ],
      'color' => $block_attributes['style']['color'] ?? [],
      'background' => $block_attributes['style']['background'] ?? [],
    ])['declarations'];

    $borderStyles = $this->getStylesFromBlock(['border' => $block_attributes['style']['border'] ?? []])['declarations'];

    if (!empty($borderStyles)) {
      $cellStyles = array_merge($cellStyles, ['border-style' => 'solid'], $borderStyles);
    }

    if (empty($cellStyles['background-size'])) {
      $cellStyles['background-size'] = 'cover';
    }

    $contentClassname = 'email_columns ' . $originalWrapperClassname;
    $contentCSS = WP_Style_Engine::compile_css($cellStyles, '');
    $layoutCSS = WP_Style_Engine::compile_css([
      'margin-top' => $parsedBlock['email_attrs']['margin-top'] ?? '0px',
      'padding-left' => $block_attributes['align'] !== 'full' ? $settingsController->getEmailStyles()['spacing']['padding']['left'] : '0px',
      'padding-right' => $block_attributes['align'] !== 'full' ? $settingsController->getEmailStyles()['spacing']['padding']['right'] : '0px',
    ], '');
    $tableWidth = $block_attributes['align'] !== 'full' ? $block_attributes['width'] : '100%';

    return '
      <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" style="width:' . esc_attr($tableWidth) . ';" width="' . esc_attr($tableWidth) . '"><tr><td style="font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
      <div style="' . esc_attr($layoutCSS) . '">
      <table style="width:100%;border-collapse:separate;" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation">
        <tbody>
          <tr>
            <td class="' . esc_attr($contentClassname) . '" style="text-align:left;width:100%;' . esc_attr($contentCSS) . '">
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse:separate;">
                <tr>
                  {columns_content}
                </tr>
              </table>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
      <!--[if mso | IE]></td></tr></table><![endif]-->
    ';
  }
}
