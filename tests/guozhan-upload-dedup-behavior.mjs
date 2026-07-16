import assert from "node:assert/strict";
import crypto from "node:crypto";
import fs from "node:fs";
import path from "node:path";
import vm from "node:vm";
import { fileURLToPath } from "node:url";

const testsDir = path.dirname(fileURLToPath(import.meta.url));
const root = path.dirname(testsDir);
const source = fs.readFileSync(
  path.join(root, "plugins", "GuozhanClientAdmin", "assets", "admin.js"),
  "utf8"
);

function extractFunction(name) {
  const signature = new RegExp(`(?:async\\s+)?function\\s+${name}\\s*\\(`);
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

const context = {
  Uint8Array,
  Map,
  Array,
  window: { crypto: crypto.webcrypto },
};
vm.createContext(context);
vm.runInContext(
  [
    extractFunction("hashBufferToHex"),
    extractFunction("hashUploadFile"),
    extractFunction("findExactDuplicateFiles"),
    extractFunction("normalizeUploadBatchOutcome"),
  ].join("\n"),
  context
);

function fakeFile(name, bytes) {
  const data = Uint8Array.from(bytes);
  return {
    name,
    async arrayBuffer() {
      return data.slice().buffer;
    },
  };
}

const sameNameA = fakeFile("same-name.png", [1, 2, 3]);
const sameNameB = fakeFile("same-name.png", [1, 2, 4]);
const differentNameSameBytes = fakeFile("different-name.png", [1, 2, 3]);
const similarButDifferent = fakeFile("similar.png", [1, 2, 5]);

const result = await context.findExactDuplicateFiles([
  sameNameA,
  sameNameB,
  differentNameSameBytes,
  similarButDifferent,
]);

assert.deepEqual(
  Array.from(result.unique, (file) => file.name),
  ["same-name.png", "same-name.png", "similar.png"],
  "Same names or similar content must not be rejected when bytes differ."
);
assert.equal(result.duplicates.length, 1);
assert.equal(result.duplicates[0].file.name, "different-name.png");
assert.equal(result.duplicates[0].original, sameNameA);
assert.equal(result.scanErrors.length, 0);

assert.deepEqual(
  { ...context.normalizeUploadBatchOutcome({ uploaded: 0, skipped_duplicates: 3 }, 3) },
  { succeeded: 0, skipped: 3, failed: 0, watermarkFailed: 0 },
  "An all-duplicate batch must finish without a false failure."
);
assert.deepEqual(
  { ...context.normalizeUploadBatchOutcome({ uploaded: 3, watermark_failed: 3 }, 3) },
  { succeeded: 0, skipped: 0, failed: 3, watermarkFailed: 3 },
  "Files without complete watermarked derivatives must not be reported as successful."
);
assert.deepEqual(
  { ...context.normalizeUploadBatchOutcome({ uploaded: 2, skipped_duplicates: 1 }, 3) },
  { succeeded: 2, skipped: 1, failed: 0, watermarkFailed: 0 }
);

console.log("PASS: only byte-identical files are skipped; names and visual similarity are ignored.");
