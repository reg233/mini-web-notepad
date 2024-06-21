/// https://github.com/markdown-it/markdown-it/blob/master/docs/architecture.md#renderer
const initMarkdownitLinkOpen = (md) => {
  // Remember the old renderer if overridden, or proxy to the default renderer.
  const defaultRender =
    md.renderer.rules.link_open ||
    ((tokens, idx, options, _, self) => {
      return self.renderToken(tokens, idx, options);
    });
  // Add target="_blank" to all other links.
  md.renderer.rules.link_open = (tokens, idx, options, env, self) => {
    try {
      const map = new Map(tokens[idx].attrs);
      const url = new URL(map.get("href"));
      if (url.origin !== window.location.origin) {
        // Add a new `target` attribute, or replace the value of the existing one.
        tokens[idx].attrSet("target", "_blank");
      }
    } catch {}

    // Pass the token to the default renderer.
    return defaultRender(tokens, idx, options, env, self);
  };
};
