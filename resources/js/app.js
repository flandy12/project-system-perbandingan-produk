import "./bootstrap";
import Alpine from "alpinejs";
import createCompareApp from "./helper";

window.Alpine = Alpine;

window.compare = createCompareApp;

Alpine.start();