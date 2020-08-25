
new autoComplete({
    data: {                              // Data src [Array, Function, Async] | (REQUIRED)
      src: async () => {
        // API key token
        const token = "this_is_the_API_token_number";
        // User search query
        const query = document.querySelector("#autoComplete").value;
        // Fetch External Data Source
        const source = await fetch("index.php?searchPersonne&q="+document.querySelector("#autoComplete").value , { method: 'POST' });
        // Format data into JSON
        const data = await source.json();
        // Return Fetched data
        return data;
      },
      key: ["nom"],
      cache: false
    },
    query: {                               // Query Interceptor               | (Optional)
          manipulate: (query) => {
            return query.replace("pizza", "burger");
          }
    },
    sort: (a, b) => {                    // Sort rendered results ascendingly | (Optional)
    	var aKey = a.nom+" "+a.prenom;
    	var bKey = b.nom+" "+b.prenom;
        return (aKey < bKey) ? -1 : 1;
    },
    placeHolder: "Personne...",     // Place Holder text                 | (Optional)
    selector: "#autoComplete",           // Input field selector              | (Optional)
    threshold: 3,                        // Min. Chars length to start Engine | (Optional)
    debounce: 300,                       // Post duration for engine to start | (Optional)
    searchEngine: "strict",              // Search Engine type/mode           | (Optional)
    resultsList: {                       // Rendered results list object      | (Optional)
        render: true,
        /* if set to false, add an eventListener to the selector for event type
           "autoComplete" to handle the result */
        container: source => {
            source.setAttribute("id", "personne_list");
        },
        destination: document.querySelector("#autoComplete"),
        position: "afterend",
        element: "ul"
    },
    maxResults: 10,                         // Max. number of rendered results | (Optional)
    highlight: true,                       // Highlight matching results      | (Optional)
    resultItem: {                          // Rendered result item            | (Optional)
        content: (data, source) => {
            source.innerHTML = data.value.nom + " " + data.value.prenom;
        },
        element: "li"
    },
    noResults: () => {                     // Action script on noResults      | (Optional)
        const result = document.createElement("li");
        result.setAttribute("class", "no_result");
        result.setAttribute("tabindex", "1");
        result.innerHTML = "Pas de correspondance";
        result.className = "autoComplete_result";
        document.querySelector("#personne_list").appendChild(result);
    },
    onSelection: feedback => {             // Action script onSelection event | (Optional)
        document.location = "index.php?show&class=Personne&idPersonne="+feedback.selection.value.idPersonne;
    }
});
