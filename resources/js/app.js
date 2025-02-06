// Import your custom scripts
import "../assets/js/custom";
import ApexCharts from "apexcharts";


// Import Choices.js
import Choices from "choices.js";
window.Choices = Choices;

// Import Preline.js
import "preline";

document.addEventListener("DOMContentLoaded", function () {
    function initializeChoices(selector) {
        const element = document.querySelector(selector);
        if (element) {
            new Choices(element, {
                removeItemButton: true,
                searchEnabled: true,
            });
        } else {
            console.warn(`Choices.js: Element ${selector} not found.`);
        }
    }

    // Ensure the element exists before initializing
    initializeChoices("#language");

    // Initialize Preline.js safely
    if (window.HSStaticMethods) {
        window.HSStaticMethods.autoInit();
    }

    // Initialize ApexCharts safely
    function initializeChart(selector) {
        const chartElement = document.querySelector(selector);
        if (chartElement) {
            const options = {
                chart: {
                    type: "line",
                    height: 350
                },
                series: [{
                    name: "Example",
                    data: [10, 20, 30, 40, 50]
                }],
                xaxis: {
                    categories: ["Jan", "Feb", "Mar", "Apr", "May"]
                }
            };

            const chart = new ApexCharts(chartElement, options);
            chart.render();
        } else {
            console.warn(`ApexCharts: Element ${selector} not found.`);
        }
    }

    // Ensure the chart container exists before initializing
    initializeChart("#chart");
});
