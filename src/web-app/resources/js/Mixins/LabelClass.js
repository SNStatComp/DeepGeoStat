export default {
    data() {
        return {
            colors: [
                '#53a31d', // Grass Green
                '#e94c0a', // Red
                '#00a1cd', // Light Blue
                '#ffcc00', // Yellow
                '#af0e80', // Purple/Pink
                '#0058b8', // Dark Blue
                '#f39200', // Orange
            ],
        }
    },

    methods: {
        add(list) {
            let color = '#FFFFFF';
            if (list.length) {
                let i = list.length;
                if (this.colors.length > i) {
                    color = this.colors[i];
                }
            } else {
                color = this.colors[0];
            }

            this.form.label_classes.push({
                id: null,
                name: '',
                color: color,
            })
        },

        remove(list, i)
        {
            list.splice(i, 1);
        }
    },
}
