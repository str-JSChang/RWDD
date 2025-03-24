document.addEventListener('DOMContentLoaded', function() {
    // Time Distribution Chart
    if (timeDistributionData && timeDistributionData.length > 0) {
        const timeDistributionCtx = document.getElementById('timeDistributionChart').getContext('2d');
        
        // Generate dynamic colors
        const backgroundColors = [
            '#3498db', '#2ecc71', '#e74c3c', '#f39c12', 
            '#9b59b6', '#1abc9c', '#34495e', '#e67e22'
        ];

        const labels = timeDistributionData.map(item => item.category_name);
        const durations = timeDistributionData.map(item => item.total_duration);

        new Chart(timeDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: durations,
                    backgroundColor: backgroundColors.slice(0, labels.length),
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const value = context.parsed;
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${context.label}: ${value} mins (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Productivity Chart
    if (productivityData && productivityData.length > 0) {
        const productivityCtx = document.getElementById('productivityChart').getContext('2d');
        
        // Prepare data for chart
        const labels = productivityData.map(item => item.day_name.substring(0, 3).toUpperCase());
        const taskCounts = productivityData.map(item => item.task_count);
        const durations = productivityData.map(item => item.total_duration);

        new Chart(productivityCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Task Count',
                        data: taskCounts,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        yAxisID: 'y-tasks'
                    },
                    {
                        label: 'Time Spent (mins)',
                        data: durations,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                        yAxisID: 'y-duration'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    'y-tasks': {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Task Count'
                        }
                    },
                    'y-duration': {
                        type: 'linear',
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Duration (mins)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });

        // Calculate additional insights
        calculateInsights(productivityData);
    }
});

function calculateInsights(data) {
    if (!data || data.length === 0) return;

    // Total tracked time
    const totalTrackedTime = data.reduce((sum, item) => sum + item.total_duration, 0);
    document.getElementById('totalTrackedTime').textContent = 
        `${(totalTrackedTime / 60).toFixed(1)} hrs`;

    // Most productive day
    const mostProductiveDay = data.reduce((max, item) => 
        (max.total_duration || 0) > item.total_duration ? max : item
    );
    document.getElementById('mostProductiveDay').textContent = 
        mostProductiveDay.day_name.charAt(0).toUpperCase() + 
        mostProductiveDay.day_name.slice(1);

    // Average daily productivity
    const avgDailyProductivity = (data.reduce((sum, item) => sum + item.task_count, 0) / data.length) * 10;
    document.getElementById('avgDailyProductivity').textContent = 
        `${avgDailyProductivity.toFixed(0)}%`;
}