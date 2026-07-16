import assert from "node:assert/strict";
import fs from "node:fs";
import path from "node:path";
import vm from "node:vm";
import { fileURLToPath } from "node:url";

const testsDir = path.dirname(fileURLToPath(import.meta.url));
const rootDir = path.dirname(testsDir);
const source = fs.readFileSync(
  path.join(rootDir, "plugins", "GuozhanClientAdmin", "assets", "admin.js"),
  "utf8"
);

function extractFunction(name) {
  const signature = new RegExp(`function\\s+${name}\\s*\\(`);
  const match = signature.exec(source);
  assert.ok(match, `Missing ${name}`);
  const start = match.index;
  const bodyStart = source.indexOf("{", start);
  let depth = 0;
  for (let index = bodyStart; index < source.length; index += 1) {
    if (source[index] === "{") depth += 1;
    if (source[index] === "}") {
      depth -= 1;
      if (depth === 0) return source.slice(start, index + 1);
    }
  }
  throw new Error(`Unterminated ${name}`);
}

class FakeClassList {
  constructor(...names) {
    this.names = new Set(names);
  }

  contains(name) {
    return this.names.has(name);
  }
}

class FakeElement {
  constructor(name, classes = []) {
    this.name = name;
    this.nodeType = 1;
    this.classList = new FakeClassList(...classes);
    this.attributes = new Map();
    this.children = [];
    this.parentNode = null;
    this.parentElement = null;
    this.hidden = false;
  }

  appendChild(child) {
    child.parentNode = this;
    child.parentElement = this;
    this.children.push(child);
    return child;
  }

  removeChild(child) {
    const index = this.children.indexOf(child);
    assert.notEqual(index, -1, "Attempted to remove a missing child.");
    this.children.splice(index, 1);
    child.parentNode = null;
    child.parentElement = null;
  }

  setAttribute(name, value) {
    this.attributes.set(name, String(value));
  }

  getAttribute(name) {
    return this.attributes.has(name) ? this.attributes.get(name) : null;
  }

  matches(selector) {
    if (selector === "#pwg_toaster .toast") {
      return this.classList.contains("toast") && Boolean(this.closest("#pwg_toaster"));
    }
    return false;
  }

  closest(selector) {
    let current = this;
    while (current) {
      if (selector === ".toast_icon" && current.classList.contains("toast_icon")) return current;
      if (selector === ".toast" && current.classList.contains("toast")) return current;
      if (selector === "#pwg_toaster" && current.getAttribute("id") === "pwg_toaster") return current;
      current = current.parentElement;
    }
    return null;
  }

  querySelector(selector) {
    return this.querySelectorAll(selector)[0] || null;
  }

  querySelectorAll(selector) {
    const results = [];
    const visit = (node) => {
      node.children.forEach((child) => {
        if (selector === ".toast_icon" && child.classList.contains("toast_icon")) results.push(child);
        if (selector === "#pwg_toaster .toast" && child.matches(selector)) results.push(child);
        visit(child);
      });
    };
    visit(this);
    return results;
  }
}

const documentElement = new FakeElement("html");
const toaster = documentElement.appendChild(new FakeElement("toaster"));
toaster.setAttribute("id", "pwg_toaster");
const toast = toaster.appendChild(new FakeElement("toast", ["toast"]));
const icon = toast.appendChild(new FakeElement("icon", ["toast_icon"]));
const nestedX = icon.appendChild(new FakeElement("x"));
const listeners = new Map();

const document = {
  documentElement,
  body: documentElement,
  addEventListener(type, listener, capture) {
    listeners.set(type, { listener, capture });
  },
};

let observer = null;
class FakeMutationObserver {
  constructor(callback) {
    observer = callback;
  }

  observe() {}
}

const context = {
  Array,
  Map,
  document,
  window: { MutationObserver: FakeMutationObserver },
  MutationObserver: FakeMutationObserver,
};
vm.createContext(context);
vm.runInContext(`${extractFunction("initDismissibleToasts")}\ninitDismissibleToasts();`, context);

assert.equal(icon.getAttribute("data-toast-dismiss-ready"), "1");
assert.equal(icon.getAttribute("role"), "button");
assert.equal(listeners.get("click").capture, true);

const click = {
  target: nestedX,
  prevented: false,
  stopped: false,
  preventDefault() {
    this.prevented = true;
  },
  stopPropagation() {
    this.stopped = true;
  },
};
listeners.get("click").listener(click);

assert.equal(click.prevented, true);
assert.equal(click.stopped, true);
assert.equal(toaster.children.includes(toast), false);
assert.equal(toast.hidden, true);
assert.equal(toast.getAttribute("aria-hidden"), "true");

const dynamicToast = toaster.appendChild(new FakeElement("dynamic-toast", ["toast"]));
const dynamicIcon = dynamicToast.appendChild(new FakeElement("dynamic-icon", ["toast_icon"]));
observer([{ addedNodes: [dynamicToast] }]);
assert.equal(dynamicIcon.getAttribute("data-toast-dismiss-ready"), "1");

console.log("PASS: nested and dynamically inserted toast close icons dismiss their notice.");
