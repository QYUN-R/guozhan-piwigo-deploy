import assert from "node:assert/strict";
import fs from "node:fs";
import path from "node:path";
import vm from "node:vm";
import { fileURLToPath } from "node:url";

const testDir = path.dirname(fileURLToPath(import.meta.url));
const scriptPath = path.join(testDir, "..", "assets", "guozhan.js");
const stylePath = path.join(testDir, "..", "assets", "guozhan.css");
const source = fs.readFileSync(scriptPath, "utf8");
const styles = fs.readFileSync(stylePath, "utf8");
const instrumentedSource = source.replace(
  "  renderStaticDirectory();",
  "  window.__GUOZHAN_TEST_HOOKS__.categories = { apiDirectoryGroups, categoryContextFromList };\n  return;\n  renderStaticDirectory();"
);
assert.notEqual(instrumentedSource, source, "The frontend initialization marker was not found.");

const document = {
  body: { hasAttribute: () => false },
  querySelector: () => null,
  querySelectorAll: () => []
};
const window = {
  location: {
    search: "",
    origin: "https://example.test",
    pathname: "/index.html"
  },
  __GUOZHAN_TEST_HOOKS__: {},
  __GUOZHAN_TEST_ONLY__: true
};
const context = {
  console,
  document,
  window,
  URL,
  URLSearchParams,
  Intl,
  Map,
  Number,
  String,
  Boolean,
  Array,
  Object,
  Math,
  Promise,
  setTimeout: () => 0,
  clearTimeout: () => {}
};
context.globalThis = context;
window.document = document;
window.window = window;

vm.runInNewContext(instrumentedSource, context, { filename: scriptPath });

const hooks = window.__GUOZHAN_TEST_HOOKS__.categories;
assert.ok(hooks, "Category test hooks were not registered.");

const categoryRows = [
  { id: 29, id_uppercat: null, name: "中美协展览专项画稿", key: "caa-exhibitions", code_prefix: "ZX", rank: 10, kind: "exhibition", reserved: false, direct_upload: true },
  { id: 39, id_uppercat: 29, name: "未知画展 07", key: "caa-unknown-07", code_prefix: "ZX-W7", rank: 10, kind: "exhibition", reserved: true, direct_upload: true },
  { id: 32, id_uppercat: 29, name: "第五届青年漆画展览征稿通知", key: "caa-young-lacquer", code_prefix: "ZX-QH", rank: 3, kind: "exhibition", reserved: false, direct_upload: true },
  { id: 34, id_uppercat: 29, name: "未知画展 02", key: "caa-unknown-02", code_prefix: "ZX-W2", rank: 5, kind: "exhibition", reserved: true, direct_upload: true },
  { id: 30, id_uppercat: 29, name: "梅花之韵——2026·中国画花鸟作品展", key: "caa-plum-blossom", code_prefix: "ZX-MH", rank: 1, kind: "exhibition", reserved: false, direct_upload: true },
  { id: 33, id_uppercat: 29, name: "未知画展 01", key: "caa-unknown-01", code_prefix: "ZX-W1", rank: 4, kind: "exhibition", reserved: true, direct_upload: true },
  { id: 31, id_uppercat: 29, name: "山水滋美——2026风景油画展", key: "caa-landscape-oil", code_prefix: "ZX-YS", rank: 2, kind: "exhibition", reserved: false, direct_upload: true }
];

const groups = hooks.apiDirectoryGroups(categoryRows);
assert.equal(groups[0].title, "中美协展览专项画稿", "The exhibition group must remain first.");
assert.deepEqual(
  Array.from(groups[0].links, (item) => item.name),
  [
    "全部作品",
    "梅花之韵——2026·中国画花鸟作品展",
    "山水滋美——2026风景油画展",
    "第五届青年漆画展览征稿通知",
    "未知画展 01",
    "未知画展 02",
    "未知画展 07"
  ],
  "Named exhibitions must stay together and reserved exhibitions must use ascending natural order."
);

const childContext = hooks.categoryContextFromList(categoryRows, "caa-plum-blossom", 30);
assert.equal(childContext.apiCategory.id, 30, "A selected exhibition child was collapsed into its parent.");
assert.equal(childContext.parentApi.id, 29, "The exhibition child lost its parent context.");
assert.equal(childContext.parentName, "中美协展览专项画稿");
assert.equal(childContext.name, "梅花之韵——2026·中国画花鸟作品展");
assert.equal(childContext.title, "梅花之韵——2026·中国画花鸟作品展");

const parentContext = hooks.categoryContextFromList(categoryRows, "caa-exhibitions", 29);
assert.equal(parentContext.apiCategory.id, 29);
assert.equal(parentContext.name, "全部作品");

assert.match(
  styles,
  /@media\s*\(max-width:\s*640px\)[\s\S]*?body\[data-category-page\]\s+\.side-panel\s*\{[^}]*display:\s*none;/,
  "Mobile category pages must not place the full category navigation before the selected works."
);

console.log("PASS: exhibition categories are visible, grouped, naturally ordered, and independently addressable.");
