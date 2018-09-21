<?php

namespace xy2z\Blader;

use eftec\bladeone\BladeOne;
use Parsedown;

/**
 * BladeOne + Markdown using Parsedown library.
 */
class BladeOneMarkdown extends BladeOne {
    private $capturing = false;

    /**
     * @markdown
     *
     */
    public function compileMarkdown($expression) {
        if (is_null($expression)) {
            $this->capturing = true;
            return "<?php ob_start(); ?>";
        }

        return "<?php echo (new Parsedown())->text($expression); ?>";
    }

    /**
     * @endmarkdown
     *
     */
    public function compileEndMarkdown() {
        if (!$this->capturing) {
            throw new \Exception("@endmarkdown without @markdown");
        }

        $this->capturing = false;
        return "<?php echo (new Parsedown())->text(ob_get_clean()); ?>";
    }
}
