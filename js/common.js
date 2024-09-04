const copyElement = document.getElementById("copy");
if (copyElement) {
  copyElement.addEventListener("click", (e) => {
    e.preventDefault();

    if (copyElement.innerText !== "Copied") {
      navigator.clipboard.writeText(getCopyText());
      copyElement.innerText = "Copied";
      setTimeout(() => {
        copyElement.innerText = "Copy";
      }, 1000);
    }
  });
}

const deleteElement = document.getElementById("delete");
if (deleteElement) {
  deleteElement.addEventListener("click", async (e) => {
    e.preventDefault();

    if (confirm("Do you really want to delete?")) {
      try {
        const response = await fetch(getDeleteUrl(), {
          headers: {
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
          },
          method: "POST",
        });
        if (response.ok) {
          window.open("/", "_self");
        } else {
          throw new Error();
        }
      } catch {
        alert("Delete failed!");
      }
    }
  });
}

const initMarkdownIt = () => {
  const md = window
    .markdownit({ html: true, linkify: true })
    .use(window.markdownitTaskLists);

  // https://github.com/markdown-it/markdown-it/blob/master/docs/architecture.md#renderer
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

  return md;
};
