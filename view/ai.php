<?php

/**
 * AI column: a real column inside the content area, never an overlay.
 * Dummy in v1, the composer answers with a fixed line (plan §5.1).
 */

?>
<aside class="col col-ai" data-role="ai" hidden>

  <div class="row row-aihead">
    <span class="ai-session">Chat 1</span>
    <div class="ai-head-actions">
      <button type="button" class="icon-btn" data-action="aiNew" title="New session">
        <svg class="icon" aria-hidden="true"><use href="styles/icons.svg#plus"></use></svg>
      </button>
      <button type="button" class="icon-btn" data-action="aiSessions" title="Sessions">
        <svg class="icon" aria-hidden="true"><use href="styles/icons.svg#chevron-down"></use></svg>
      </button>
    </div>
  </div>

  <div class="ai-messages" data-role="aiMessages">
    <div class="ai-hint">Not connected yet. The layout is real, the answers are not.</div>
  </div>

  <form class="ai-composer" data-role="aiComposer">
    <textarea class="ai-input" rows="1" placeholder="Ask something…" aria-label="Message"></textarea>
    <button type="submit" class="icon-btn ai-send" title="Send">
      <svg class="icon" aria-hidden="true"><use href="styles/icons.svg#send"></use></svg>
    </button>
  </form>

</aside>
