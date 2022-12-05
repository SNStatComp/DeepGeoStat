export default {
    methods: {
        convertDataForScatter(data) {
            const types = data
                .map((item) => item.type)
                .filter((value, index, self) => self.indexOf(value) === index);

            data = types.map((t) => {
                const d = data.filter((r) => r.type === t);

                return {
                    mode: "lines",
                    type: "scatter",
                    name: t,
                    x: d.map((r) => r.epoch),
                    y: d.map((r) => r.value),
                };
            });
            return data;
        },
    }
}