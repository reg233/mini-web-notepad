function uploadContent() {
  if (content !== textarea.value) {
    const temp = textarea.value;
    const request = new XMLHttpRequest();
    request.open("POST", window.location.href, true);
    request.setRequestHeader(
      "Content-Type",
      "application/x-www-form-urlencoded; charset=UTF-8"
    );
    request.onload = function () {
      if (request.readyState === 4) {
        content = temp;
        setTimeout(uploadContent, 1000);
      }
    };
    request.onerror = function () {
      setTimeout(uploadContent, 1000);
    };
    request.send("text=" + encodeURIComponent(temp));
  } else {
    setTimeout(uploadContent, 1000);
  }
}

const textarea = document.getElementById("textarea");
let content = textarea.value;
const markdown = document.getElementById("markdown");

uploadContent();

const md = window
  .markdownit({ html: true, linkify: true })
  .use(window.markdownitTaskLists);

// Remember the old renderer if overridden, or proxy to the default renderer.
const defaultRender =
  md.renderer.rules.link_open ||
  function (tokens, idx, options, _, self) {
    return self.renderToken(tokens, idx, options);
  };
// Add target="_blank" to all other links
md.renderer.rules.link_open = function (tokens, idx, options, env, self) {
  try {
    const map = new Map(tokens[idx].attrs);
    const url = new URL(map.get("href"));
    if (url.origin !== window.location.origin) {
      // Add a new `target` attribute, or replace the value of the existing one.
      tokens[idx].attrSet("target", "_blank");
    }
  } catch (error) {}

  // Pass the token to the default renderer.
  return defaultRender(tokens, idx, options, env, self);
};

markdown.innerHTML = md.render(content);
textarea.addEventListener("input", function (e) {
  markdown.innerHTML = md.render(e.target.value);
});

function initSplit() {
  return Split(["#textarea", "#markdown"], {
    direction,
    gutter: function (_, direction) {
      const gutter = document.createElement("div");
      gutter.className = `gutter gutter-${direction}`;

      const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
      svg.setAttribute("width", "16");
      svg.setAttribute("height", "16");
      svg.setAttribute("fill", "currentColor");
      svg.setAttribute("viewBox", "0 0 16 16");
      const path = document.createElementNS(
        "http://www.w3.org/2000/svg",
        "path"
      );
      if (direction === "horizontal") {
        path.setAttribute(
          "d",
          "M7 2a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M7 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M7 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0m-3 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0m-3 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0m3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0"
        );
      } else {
        path.setAttribute(
          "d",
          "M2 8a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2m0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2"
        );
      }
      svg.appendChild(path);

      gutter.appendChild(svg);

      return gutter;
    },
    gutterSize: 16,
    sizes,
    onDragEnd: function (e) {
      sizes = e;
      localStorage.setItem(
        `split-sizes${window.location.pathname}`,
        JSON.stringify(e)
      );
    },
  });
}

let direction = window.innerWidth > 789 ? "horizontal" : "vertical";
let sizes = localStorage.getItem(`split-sizes${window.location.pathname}`);
if (sizes) {
  sizes = JSON.parse(sizes);
} else {
  sizes = [50, 50];
}
let split = initSplit();

window.addEventListener("resize", function () {
  const newDirection = window.innerWidth > 789 ? "horizontal" : "vertical";
  if (newDirection !== direction) {
    direction = newDirection;
    split.destroy();
    split = initSplit();
  }
});
