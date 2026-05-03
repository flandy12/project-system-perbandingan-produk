import "./bootstrap";
import Alpine from "alpinejs";
import createCompareApp from "./helper";

window.Alpine = Alpine;

// 🔥 expose ke Alpine
window.compare = createCompareApp();

Alpine.start();