export async function prefillByICO(ico) {
    const response = await fetch(`https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/${ico}`);
    if (!response.ok) throw new Error('API call failed');
    
    const data = await response.json();
    
    // Map the response to the required format
    return {
        title: data.obchodniJmeno || '',
        ico: data.ico || '',
        dic: data.dic || '',
        street: `${data.sidlo.nazevUlice} ${data.sidlo.cisloDomovni}/${data.sidlo.cisloOrientacni}`,
        city: `${data.sidlo.nazevObce}${data.sidlo.nazevCastiObce ? ' - ' + data.sidlo.nazevCastiObce : ''}`,
        psc: data.sidlo.psc || ''
    };
}
