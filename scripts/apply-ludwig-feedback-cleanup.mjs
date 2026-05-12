import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');
const write = (file, value) => fs.writeFileSync(path.join(root, file), value, 'utf8');

const footerInserts = [
  {
    href: 'unternehmervollmacht.html',
    markup: '<a href="unternehmervollmacht.html">Unternehmervollmacht</a>',
    after: /(<a href="kontakt\.html">Kontakt &amp; Termin<\/a>)/,
  },
  {
    href: 'ankuendigung.html',
    markup: '<a href="ankuendigung.html">Ank&uuml;ndigung</a>',
    after: /(<a href="unternehmervollmacht\.html">Unternehmervollmacht<\/a>)/,
  },
  {
    href: 'schadenfall.html',
    markup: '<a href="schadenfall.html">Schadenfall</a>',
    after: /(<a href="ankuendigung\.html">Ank&uuml;ndigung<\/a>)/,
  },
  {
    href: 'durchblick.html',
    markup: '<a href="durchblick.html">Durchblick</a>',
    after: /(<a href="schadenfall\.html">Schadenfall<\/a>)/,
  },
];
const calendlyLinks = {
  zusammenarbeit: 'https://calendly.com/einsparung/zusammenarbeit',
  beratung: 'https://calendly.com/einsparung/beratung',
  telefon: 'https://calendly.com/einsparung/telefon',
  notfallplanung: 'https://calendly.com/einsparung/notfallplanung',
  weitereFragen: 'https://calendly.com/einsparung/weitere-fragen',
};
const durchblickLinks = {
  kostenvergleich: 'https://img1.wsimg.com/blobby/go/bf02664e-696f-43a3-81e6-61e8b405fc2a/downloads/d72fe84e-e3e7-4873-9e03-ebf774ec6f70/Steuervergleich%20mit%20TradeRepuiblic.pdf?ver=1777982447724',
  produktinformationen: 'https://img1.wsimg.com/blobby/go/bf02664e-696f-43a3-81e6-61e8b405fc2a/downloads/53ac43b6-c051-4705-ba4d-f2c83b3f639c/Produktinformationen.pdf?ver=1777982447724',
};
const currentStand = (() => {
  const now = new Date();
  return {
    label: new Intl.DateTimeFormat('de-DE', { month: 'long', year: 'numeric' }).format(now),
    iso: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`,
  };
})();
const facebookFooterLink = `<a aria-label="Facebook" href="https://www.facebook.com/ludwig.finanzmakler/" target="_blank" rel="noopener noreferrer">
<svg fill="currentColor" height="20" viewBox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
<path d="M14 8.5V6.75C14 6.05 14.5 6 14.9 6H17V2.2C16.1 2.08 15.2 2 14.3 2C11.6 2 9.7 3.65 9.7 6.65V8.5H6.6V12.75H9.7V22H14V12.75H17.15L17.75 8.5H14Z"></path>
</svg>
</a>`;
const removedFooterLinks = [
  /[ \t]*<a href="teilnahmebedingungen\.html">Teilnahmebedingungen<\/a>\r?\n?/g,
  /[ \t]*<a href="vorgangsabfrage\.html">Vorgangsabfrage<\/a>\r?\n?/g,
];

const migratedPages = [
  'berufsunfaehigkeit.html',
  'haftpflichtversicherung.html',
  'hausratversicherung.html',
  'unfallversicherung.html',
  'gebaeudeversicherung.html',
  'rentenversicherung.html',
  'pflegeversicherung.html',
  'krankenversicherung.html',
  'rechtsschutzversicherung.html',
  'zahnzusatzversicherung.html',
  'expat-beratung-1.html',
  'digitale-nomaden.html',
  'immobilien-nomaden.html',
  'freelancer-nomaden.html',
  'spanien-dubai.html',
];

function listRootHtml() {
  return fs.readdirSync(root)
    .filter((file) => file.endsWith('.html'))
    .filter((file) => file !== 'scratch.html');
}

function cleanFooter(html) {
  html = normalizeAppointmentLinks(html);
  html = normalizeFooterLanguage(html);
  html = normalizeDurchblickBranding(html);

  for (const pattern of removedFooterLinks) {
    html = html.replace(pattern, '');
  }

  for (const insert of footerInserts) {
    if (!html.includes(`href="${insert.href}"`)) {
      html = html.replace(
        insert.after,
        `$1\n                ${insert.markup}`
      );
    }
  }

  if (!html.includes('facebook.com/ludwig.finanzmakler')) {
    html = html.replace(
      /(<a[^>]+aria-label="WhatsApp"[^>]*>[\s\S]*?<\/a>)/,
      `$1\n${facebookFooterLink}`
    );
  }

  return normalizeFooterMehrOrder(normalizeDurchblickBranding(html));
}

function normalizeFooterLanguage(html) {
  return html
    .replace(/<h4 class="footer-title">More<\/h4>/g, '<h4 class="footer-title">Mehr</h4>')
    .replace(/<a href="zusammenarbeit\.html">How we work<\/a>/g, '<a href="zusammenarbeit.html">Zusammenarbeit</a>')
    .replace(/<a href="gold-service\.html">Gold Service<\/a>/g, '<a href="gold-service.html">Gold-Service</a>')
    .replace(/<a href="familien\.html">Families<\/a>/g, '<a href="familien.html">F&uuml;r Familien</a>')
    .replace(/<a href="selbststaendige\.html">Self-employed<\/a>/g, '<a href="selbststaendige.html">F&uuml;r Selbstst&auml;ndige</a>')
    .replace(/<a href="ueber-ludwig\.html">About Ludwig<\/a>/g, '<a href="ueber-ludwig.html">&Uuml;ber Ludwig</a>')
    .replace(/<a href="wissen\.html">Knowledge &amp; guides<\/a>/g, '<a href="wissen.html">Wissen &amp; Ratgeber</a>')
    .replace(/<a href="kontakt\.html">Contact &amp; appointment<\/a>/g, '<a href="kontakt.html">Kontakt &amp; Termin</a>')
    .replace(/<a href="unternehmervollmacht\.html">Business power of attorney<\/a>/g, '<a href="unternehmervollmacht.html">Unternehmervollmacht</a>')
    .replace(/<a href="berufsunfaehigkeit\.html">Disability insurance<\/a>/g, '<a href="berufsunfaehigkeit.html">Berufsunf&auml;higkeit</a>')
    .replace(/<a href="haftpflichtversicherung\.html">Liability insurance<\/a>/g, '<a href="haftpflichtversicherung.html">Haftpflichtversicherung</a>')
    .replace(/<a href="hausratversicherung\.html">Home contents insurance<\/a>/g, '<a href="hausratversicherung.html">Hausratversicherung</a>')
    .replace(/<a href="unfallversicherung\.html">Accident insurance<\/a>/g, '<a href="unfallversicherung.html">Unfallversicherung</a>')
    .replace(/<a href="gebaeudeversicherung\.html">Building insurance<\/a>/g, '<a href="gebaeudeversicherung.html">Geb&auml;udeversicherung</a>')
    .replace(/<a href="rentenversicherung\.html">Pension insurance<\/a>/g, '<a href="rentenversicherung.html">Rentenversicherung</a>')
    .replace(/<a href="pflegeversicherung\.html">Long-term care insurance<\/a>/g, '<a href="pflegeversicherung.html">Pflegeversicherung</a>')
    .replace(/<a href="krankenversicherung\.html">Health insurance<\/a>/g, '<a href="krankenversicherung.html">Krankenversicherung</a>')
    .replace(/<a href="rechtsschutzversicherung\.html">Legal protection insurance<\/a>/g, '<a href="rechtsschutzversicherung.html">Rechtsschutzversicherung</a>')
    .replace(/<a href="zahnzusatzversicherung\.html">Dental insurance<\/a>/g, '<a href="zahnzusatzversicherung.html">Zahnzusatzversicherung</a>')
    .replace(/<a href="expat-beratung-1\.html">Expat advice<\/a>/g, '<a href="expat-beratung-1.html">Expat-Beratung</a>')
    .replace(/<a href="expat-beratung-1\.html">Expat consulting<\/a>/g, '<a href="expat-beratung-1.html">Expat-Beratung</a>')
    .replace(/<a href="digitale-nomaden\.html">Digital nomads<\/a>/g, '<a href="digitale-nomaden.html">Digitale Nomaden</a>')
    .replace(/<a href="immobilien-nomaden\.html">Real estate entrepreneurs<\/a>/g, '<a href="immobilien-nomaden.html">Immobilien-Unternehmer</a>')
    .replace(/<a href="freelancer-nomaden\.html">Freelancers<\/a>/g, '<a href="freelancer-nomaden.html">Freelancer &amp; Selbst&auml;ndige</a>')
    .replace(/<a href="freelancer-nomaden\.html">Freelancers &amp; self-employed<\/a>/g, '<a href="freelancer-nomaden.html">Freelancer &amp; Selbst&auml;ndige</a>')
    .replace(/<a href="spanien-dubai\.html">Spain - Dubai - Germany<\/a>/g, '<a href="spanien-dubai.html">Spanien - Dubai - Deutschland</a>');
}

function normalizeFooterMehrOrder(html) {
  return html.replace(
    /(<a href="kontakt\.html">Kontakt &amp; Termin<\/a>)[\s\S]*?(<a href="berufsunfaehigkeit\.html">)/g,
    `$1
                <a href="unternehmervollmacht.html">Unternehmervollmacht</a>
                <a href="ankuendigung.html">Ank&uuml;ndigung</a>
                <a href="schadenfall.html">Schadenfall</a>
                <a href="durchblick.html">Durchblick</a>
                $2`
  );
}

function normalizeDurchblickBranding(html) {
  return html
    .replace(/<a href="durchblick\.html">Durchblick ETF-Vorsorge<\/a>/g, '<a href="durchblick.html">Durchblick</a>')
    .replace(/DURCHBLICK ETF-Vorsorge/g, 'Durchblick ETF-Vorsorge')
    .replace(/DURCHBLICK ansehen/g, 'Durchblick ansehen')
    .replace(/DURCHBLICK\?/g, 'Durchblick?')
    .replace(/Mit DURCHBLICK/g, 'Mit Durchblick')
    .replace(/ob DURCHBLICK/g, 'ob Durchblick')
    .replace(/Wenn Du DURCHBLICK/g, 'Wenn Du Durchblick')
    .replace(/Was ist DURCHBLICK\?/g, 'Was ist Durchblick?')
    .replace(/Flexibilit&auml;t ist ein zentraler Punkt von DURCHBLICK/g, 'Flexibilit&auml;t ist ein zentraler Punkt von Durchblick')
    .replace(/DURCHBLICK ist/g, 'Durchblick ist')
    .replace(/DURCHBLICK kurz/g, 'Durchblick kurz');
}

function normalizeAppointmentLinks(html) {
  html = html
    .replace(/https:\/\/calendly\.com\/einsparung\/themenberatung/g, calendlyLinks.beratung)
    .replace(/https:\/\/calendly\.com\/einsparung\/beratung\?back=1/g, calendlyLinks.beratung)
    .replace(/https:\/\/calendly\.com\/einsparung\/weiterefragen/g, calendlyLinks.telefon);

  html = html.replace(
    /(<h3>Zusammenarbeit<\/h3>[\s\S]*?<a href=")https:\/\/calendly\.com\/einsparung(?:\/telefon)?(")/g,
    `$1${calendlyLinks.zusammenarbeit}$2`
  );
  html = html.replace(
    /(<h3>Beratung<\/h3>[\s\S]*?<a href=")https:\/\/calendly\.com\/einsparung(?:"|\/beratung")/g,
    `$1${calendlyLinks.beratung}"`
  );
  html = html.replace(
    /(<h3>Notfallplanung<\/h3>[\s\S]*?<a href=")https:\/\/calendly\.com\/einsparung(?:"|\/beratung")/g,
    `$1${calendlyLinks.notfallplanung}"`
  );

  return html;
}

function removeWorumSection(html) {
  return html.replace(
    /\s*<section class="section bg-white">\s*<div class="container(?: container-narrow)?">\s*<div class="section-header section-header-left reveal"><h2>Worum es auf dieser Seite geht<\/h2><\/div>[\s\S]*?<\/div>\s*<\/section>/g,
    ''
  );
}

function cleanMigrationWording(html) {
  const replacements = [
    ['Die Live-Seite hob au&szlig;erdem', 'Wichtig ist außerdem'],
    ['Die Live-Seite schloss genau mit diesem Gedanken:', 'Am Ende zählt genau dieser Gedanke:'],
    ['Die Live-Seite war deutlich tiefer aufgebaut und behandelte nicht nur', 'Eine gute Unfallversicherung betrachtet nicht nur'],
    ['Die Live-Seite stellte klar heraus, dass', 'Wichtig ist, dass'],
    ['Einen gro&szlig;en Teil der Live-Seite machten genau diese Feinheiten aus:', 'Ein großer Teil der Prüfung liegt in genau diesen Feinheiten:'],
    ['Die Live-Seite war deutlich umfassender als die bisherige lokale Fassung. Sie behandelte', 'Diese Beratung betrachtet'],
    ['Die Live-Seite erkl&auml;rte sehr deutlich, dass', 'Wichtig ist, dass'],
    ['Die Live-Seite stellte die drei klassischen Wege sauber nebeneinander:', 'Die drei klassischen Wege sollten sauber getrennt werden:'],
    ['Ein weiterer Schwerpunkt der Live-Seite war', 'Ein weiterer Schwerpunkt ist'],
    ['Die Live-Seite arbeitete bewusst mit Lebensphasen.', 'Gute Pflegevorsorge arbeitet bewusst mit Lebensphasen.'],
    ['Die Live-Seite war bewusst viel tiefer:', 'Wichtig sind vor allem:'],
    ['Die Live-Seite stellte den Zugang zur PKV systematisch dar.', 'Der Zugang zur PKV sollte systematisch geprüft werden.'],
    ['Die Live-Seite war auch hier deutlich klarer:', 'Auch hier ist wichtig:'],
    ['Die Live-Seite stellte au&szlig;erdem', 'Wichtig sind außerdem'],
    ['Die Live-Seite war deutlich umfangreicher und zeigte,', 'Diese Beratung zeigt,'],
    ['Die Live-Seite trennte', 'Eine gute Prüfung trennt'],
    ['Die Live-Seite machte außerdem deutlich, dass', 'Wichtig ist außerdem, dass'],
    ['Die Live-Seite machte au&szlig;erdem deutlich, dass', 'Wichtig ist außerdem, dass'],
    ['Die Live-Seite machte deutlich, dass', 'Wichtig ist, dass'],
    ['Die Live-Seite erklärte auch', 'Wichtig ist auch'],
    ['Die Live-Seite erkl&auml;rte auch', 'Wichtig ist auch'],
    ['Die Live-Seite war inhaltlich deutlich umfangreicher als die erste lokale Fassung. Sie erkl&auml;rte', 'Diese Seite erklärt'],
    ['Die Live-Seite machte die klassische Unterscheidung sehr klar:', 'Die klassische Unterscheidung ist klar:'],
    ['Neben der Grunddeckung hob die Live-Seite besonders', 'Neben der Grunddeckung sind besonders'],
    ['Ein weiterer Schwerpunkt der Live-Seite war die richtige Versicherungssumme.', 'Ein weiterer Schwerpunkt ist die richtige Versicherungssumme.'],
    ['Auch dieser Punkt kam auf der Live-Seite ausf&uuml;hrlich vor:', 'Auch dieser Punkt ist wichtig:'],
    ['Die Live-Seite war viel klarer auf echte Expat-Fragen in Dubai ausgerichtet:', 'Diese Beratung ist klar auf echte Expat-Fragen in Dubai ausgerichtet:'],
    ['Die fr&uuml;here Live-Seite war keine allgemeine Expat-Landingpage, sondern bewusst fokussiert:', 'Diese Seite ist keine allgemeine Expat-Landingpage, sondern bewusst fokussiert:'],
    ['Die Live-Seite argumentierte aus Expat-Praxis heraus:', 'Die Beratung argumentiert aus Expat-Praxis heraus:'],
    ['Die Live-Seite nannte sehr konkret die Zielgruppen:', 'Die Zielgruppen sind sehr konkret:'],
    ['Die Live-Seite war viel genauer darin, was eigentlich beraten wird:', 'Entscheidend ist, was konkret beraten wird:'],
    ['Auch diesen Teil trug die Live-Seite deutlich stärker:', 'Auch dieser Teil ist wichtig:'],
    ['Ja, genau das betonte auch die Live-Seite.', 'Ja.'],
    ['Ja. Die Live-Seite war genau dafür gebaut.', 'Ja.'],
    ['Die Live-Seite war deutlich reichhaltiger und sprach viel klarer über', 'Eine gute Beratung betrachtet'],
    ['Die alte Live-Seite war spürbar näher an der Realität digitaler Nomaden:', 'Digitale Nomaden bewegen sich oft zwischen mehreren Ländern:'],
    ['Warum die Live-Seite für Nomaden deutlich tiefer war', 'Warum Nomaden-Setups genauer geprüft werden müssen'],
    ['genau das war die Kernbotschaft der Live-Seite auch für Nomaden-Setups.', 'genau das ist der Kern bei Nomaden-Setups.'],
    ['Die Live-Seite richtete sich sichtbar an', 'Diese Seite richtet sich an'],
    ['Die Live-Seite legte stärker offen, worin die eigentliche Komplexität liegt:', 'Die eigentliche Komplexität liegt in'],
    ['Die Live-Seite sprach viel klarer über Lösungsarten:', 'Wichtig ist die Unterscheidung der Lösungsarten:'],
    ['Die Live-Seite war besonders stark bei den praktischen Missverständnissen:', 'Besonders wichtig sind die praktischen Missverständnisse:'],
    ['Die Live-Seite war wesentlich konkreter bei genau dieser Kombination:', 'Diese Beratung ist konkret auf diese Kombination ausgerichtet:'],
    ['Die alte Live-Seite war keine allgemeine Nomaden-Seite, sondern auffallend pr&auml;zise auf', 'Diese Seite ist keine allgemeine Nomaden-Seite, sondern präzise auf'],
    ['Die Live-Seite machte klar, dass', 'Wichtig ist, dass'],
    ['Das Zielbild der Live-Seite war klar:', 'Das Zielbild ist klar:'],
    ['Die Live-Seite benannte die Zielgruppe sehr klar:', 'Die Zielgruppe ist klar:'],
    ['Die Live-Seite war deutlicher darin, dass', 'Wichtig ist, dass'],
    ['Die Live-Seite war besonders stark darin, auf praktische Fehler hinzuweisen:', 'Besonders wichtig ist der Blick auf praktische Fehler:'],
    ['Wie auf der Live-Seite geht es auch hier nicht um Theorie allein.', 'Es geht nicht um Theorie allein.'],
    ['Genau diese Annahme f&uuml;hrte auf der Live-Seite zu vielen der typischen Problemf&auml;lle.', 'Genau diese Annahme führt oft zu typischen Problemfällen.'],
    ['Die Live-Seite war bei dieser Zielgruppe deutlich konkreter:', 'Diese Beratung ist bei dieser Zielgruppe besonders konkret:'],
    ['Die alte Live-Seite war klar auf Unternehmer mit Immobilien- und Standortbezug in mehreren Ländern zugeschnitten.', 'Immobilien-Unternehmer mit mehreren Standorten brauchen eine l&auml;nder&uuml;bergreifende Einordnung.'],
    ['Warum diese Beratung auf der Live-Seite so spezifisch war', 'Warum Mehr-Länder-Fälle spezifisch geprüft werden müssen'],
    ['Deutschland, Spanien und Dubai wurden auf der Live-Seite bewusst zusammen betrachtet,', 'Deutschland, Spanien und Dubai werden bewusst zusammen betrachtet,'],
    ['Genau wie auf der Live-Seite geht es darum,', 'Es geht darum,'],
    ['Die Live-Seite war hier konkreter als die lokale Fassung:', 'Entscheidend ist:'],
    ['Auch das brachte die Live-Seite deutlicher heraus:', 'Auch das ist wichtig:'],
    ['Die Live-Seite war sehr bewusst darin, genau diese Zielgruppe anzusprechen und nicht alles für alle anbieten zu wollen.', 'Die Beratung bleibt bewusst auf diese Zielgruppe fokussiert und will nicht alles für alle anbieten.'],
    ['Die Live-Seite war klar auf Mehr-Länder-Fälle zugeschnitten:', 'Diese Beratung ist klar auf Mehr-Länder-Fälle zugeschnitten:'],
    ['Die ursprüngliche Live-Seite war viel näher an echten Multi-Country-Fällen als die erste lokale Fassung.', 'Echte Multi-Country-Fälle brauchen eine konkrete Betrachtung.'],
    ['Die Themen, die auf der Live-Seite im Mittelpunkt standen', 'Die Themen, die im Mittelpunkt stehen'],
    ['Die Live-Seite sprach ausdrücklich von typischen Fällen wie', 'Typische Fälle sind'],
    ['Die Live-Seite war stark darin, nicht direkt mit Tarifen einzusteigen, sondern mit Regeln:', 'Wichtig ist, nicht direkt mit Tarifen einzusteigen, sondern mit Regeln:'],
    ['Die Live-Seite verband Preis-CTAs bewusst mit Beratung,', 'Preisfragen gehören bewusst in eine Beratung,'],
    ['Die Live-Seite war deutlich l&auml;nger und behandelte nicht nur', 'Diese Seite betrachtet nicht nur'],
    ['Die Live-Seite erkl&auml;rte sehr ausf&uuml;hrlich', 'Wichtig ist'],
    ['Im Alltag wird oft nur an die Altersrente gedacht. Die Live-Seite zeigte aber zu Recht ein breiteres Bild:', 'Im Alltag wird oft nur an die Altersrente gedacht. Das vollständige Bild ist breiter:'],
    ['Auch bei der Berechnung war die Live-Seite viel tiefer:', 'Auch bei der Berechnung lohnt der genaue Blick:'],
    ['Die Live-Seite schlug bewusst die Br&uuml;cke', 'Wichtig ist die Brücke'],
    ['The original live page contained much more day-to-day context:', 'This page focuses on the practical day-to-day context:'],
    ['What the live page emphasized most', 'What matters most'],
    ['How PassportCard was presented for everyday use in Dubai', 'How PassportCard works in everyday Dubai life'],
    ['Why Dubai expats looked at PassportCard on the live page', 'Why Dubai expats look at PassportCard'],
  ];

  for (const [from, to] of replacements) {
    html = html.split(from).join(to);
  }

  html = html
    .replace(/alte Footerseite/gi, 'frühere Produktseite')
    .replace(/alten Seite/g, 'Produktseite')
    .replace(/alten Live-Seite/g, 'Produktseite')
    .replace(/alte Live-Seite/g, 'Produktseite')
    .replace(/frühere Live-Seite/g, 'Produktseite')
    .replace(/ursprüngliche Live-Seite/g, 'Produktseite')
    .replace(/Live-Seite/g, 'Seite')
    .replace(/live page/gi, 'page');

  return html;
}

function finalUserFacingCopyCleanup(html) {
  const replacements = [
    ['Die alte Seite betonte zu Recht, dass der Antrag kein Ort f&uuml;r Sch&auml;tzungen ist.', 'Beim Antrag gilt: Er ist kein Ort f&uuml;r Sch&auml;tzungen.'],
    ['Die frühere Seite argumentierte bewusst mit Spezialisierung. Das ergibt Sinn, weil', 'Spezialisierung ist hier wichtig, weil'],
    ['Du kannst die Beratung wie auf der Produktseite direkt per WhatsApp anstoßen oder Dir einen Termin im Kalender sichern. Für die Community-Anbindung bleibt auch die frühere Facebook-Gruppe erreichbar.', 'Du kannst die Beratung direkt per WhatsApp anstoßen oder Dir einen Termin im Kalender sichern. Wenn eine Community-Anbindung sinnvoll ist, beziehe ich sie in die Einordnung mit ein.'],
    ['Die alte Footer-Seite war sehr zugespitzt, inhaltlich aber klar: Unterversicherung, fehlender Elementarschutz und veraltete Policen können im Großschaden extrem teuer werden.', 'Bei Wohngebäuden wird es schnell teuer: Unterversicherung, fehlender Elementarschutz und veraltete Policen können im Großschaden erhebliche finanzielle Folgen haben.'],
    ['Die Live-Inhalte unterschieden klar zwischen', 'Für die Einordnung unterscheide ich klar zwischen'],
    ['Die fr&uuml;here Seite nannte auch die typischen Ausschl&uuml;sse:', 'Typische Ausschl&uuml;sse sind ebenfalls wichtig:'],
    ['Die Live-Inhalte gingen weit &uuml;ber allgemeine Schlagworte hinaus.', 'Eine gute Beratung geht weit &uuml;ber allgemeine Schlagworte hinaus.'],
    ['Die alte Footer-Seite war zugespitzt, aber der Kern stimmt:', 'Der Kern ist einfach:'],
    ['Wenn Du einen Tarif prüfen oder direkt einen passenden Einstieg finden willst, kannst Du den alten Footer-Flow weiterhin nutzen oder Dich vorher kurz mit mir abstimmen.', 'Wenn Du einen Tarif prüfen oder direkt einen passenden Einstieg finden willst, kannst Du den passenden Einstieg nutzen oder Dich vorher kurz mit mir abstimmen.'],
    ['Die fr&uuml;here Seite nannte ausdr&uuml;cklich Bergungs- und Rettungskosten, kosmetische Operationen sowie weitere Erg&auml;nzungen.', 'Wichtig sind au&szlig;erdem Bergungs- und Rettungskosten, kosmetische Operationen sowie weitere Erg&auml;nzungen.'],
    ['Die Live-Inhalte unterschieden sauber nach Kindern, Berufst&auml;tigen, Senioren sowie sportlich aktiven Menschen.', 'Eine saubere Einordnung unterscheidet nach Kindern, Berufst&auml;tigen, Senioren sowie sportlich aktiven Menschen.'],
    ['Sehr oft ja. Genau das wurde auf der Seite klarer benannt: Längere Aufenthalte oder eine Residencia verändern die Anforderungen deutlich.', 'Sehr oft ja. Längere Aufenthalte oder eine Residencia verändern die Anforderungen deutlich.'],
    ['This is the everyday advantage that made PassportCard stand out on the original product page.', 'This practical payment logic is the reason PassportCard can be so relevant for expats.'],
    ['Die inhaltliche Grundlage stammt aus der früheren Footer-Seite. Struktur und Lesbarkeit wurden an die aktuelle Ludwig-Seite angepasst; Ansprechpartner und Kontaktdaten orientieren sich am aktuellen lokalen Projekt.', 'Die Teilnahmebedingungen sind klar strukturiert zusammengefasst; Ansprechpartner und Kontaktdaten orientieren sich am aktuellen Ludwig-Projekt.'],
  ];

  for (const [from, to] of replacements) {
    html = html.split(from).join(to);
  }

  html = html
    .replace(/Rheinstra&szlig;e 80/g, 'Bismarckstra&szlig;e 26')
    .replace(/Rheinstraße 80/g, 'Bismarckstraße 26')
    .replace(/76532 Baden-Baden/g, '76530 Baden-Baden')
    .replace(/info@oelze-findet-einsparung\.de/g, 'Finanzen@ludwigoelze.com')
    .replace(/<p>Quellen:<\/p>/g, '')
    .replace(/Pers&ouml;nliche Beratungsgespr&auml;che in meinem B&uuml;ro in der Bismarckstra&szlig;e 26 in Baden-Baden/g, 'Pers&ouml;nliche Beratungsgespr&auml;che nach Terminvereinbarung')
    .replace(/Persönliche Beratungsgespräche in meinem Büro in der Bismarckstraße 26 in Baden-Baden/g, 'Persönliche Beratungsgespräche nach Terminvereinbarung')
    .replace(/<li><span>Adresse: Bismarckstra&szlig;e 26, 76530 Baden-Baden<\/span><\/li>/g, '')
    .replace(/<li><span>Adresse: Bismarckstraße 26, 76530 Baden-Baden<\/span><\/li>/g, '')
    .replace(/Diese Seite spricht klar über/g, 'Eine gute Beratung betrachtet')
    .replace(/Diese Seite spricht bewusst genau diese Zielgruppe an und will nicht alles für alle anbieten\./g, 'Die Beratung bleibt bewusst auf diese Zielgruppe fokussiert und will nicht alles für alle anbieten.')
    .replace(/Warum diese Beratung auf der Seite so klar positioniert war/g, 'Warum die Beratung klar positioniert ist')
    .replace(/Warum diese Beratung so spezifisch sein muss/g, 'Warum Mehr-L&auml;nder-F&auml;lle spezifisch gepr&uuml;ft werden m&uuml;ssen')
    .replace(/https:\/\/www\.google\.com\/maps\/search\/\?api=1&amp;query=Ludwig%20Oelze%20Rheinstra%C3%9Fe%2080%2076532%20Baden-Baden/g, 'https://www.google.com/maps/search/?api=1&amp;query=Ludwig%20Oelze%20Bismarckstra%C3%9Fe%2026%2076530%20Baden-Baden');

  return normalizeDurchblickBranding(html);
}

function extractPageShell(file) {
  const html = read(file);
  const mainStart = html.indexOf('<main>');
  const mainEnd = html.indexOf('</main>');
  if (mainStart === -1 || mainEnd === -1) {
    throw new Error(`No main element found in ${file}`);
  }
  return {
    before: html.slice(0, mainStart),
    after: html.slice(mainEnd + '</main>'.length),
  };
}

function replaceMain(file, mainHtml, options = {}) {
  const { before, after } = extractPageShell(file);
  let html = before + mainHtml + after;
  if (options.title) {
    html = html.replace(/<title>[\s\S]*?<\/title>/, `<title>${options.title}</title>`);
  }
  if (options.description) {
    html = html.replace(/<meta content="[^"]*" name="description"\/?>/, `<meta content="${options.description}" name="description"/>`);
    html = html.replace(/<meta name="description" content="[^"]*"\/?>/, `<meta name="description" content="${options.description}"/>`);
  }
  if (options.bodyClass) {
    html = html.replace(/<body class="[^"]*">/, `<body class="${options.bodyClass}">`);
  }
  if (options.lang) {
    html = html.replace(/<html lang="[^"]*">/, `<html lang="${options.lang}">`);
  }
  write(file, cleanFooter(html));
}

const icon = {
  globe: '<svg fill="none" height="28" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="28" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10"></circle><line x1="2" x2="22" y1="12" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>',
  card: '<svg fill="none" height="28" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="28" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="5" width="20" height="14" rx="2"></rect><path d="M2 10h20"></path></svg>',
  clock: '<svg fill="none" height="28" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="28" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
  shield: '<svg fill="none" height="28" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="28" xmlns="http://www.w3.org/2000/svg"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
  check: '<svg fill="none" height="28" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="28" xmlns="http://www.w3.org/2000/svg"><polyline points="20 6 9 17 4 12"></polyline></svg>',
  file: '<svg fill="none" height="28" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="28" xmlns="http://www.w3.org/2000/svg"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>',
};

function card(title, text, iconSvg = icon.check, extra = '') {
  return `<div class="card reveal ${extra}">
<div class="card-icon">${iconSvg}</div>
<h3>${title}</h3>
<p>${text}</p>
</div>`;
}

function list(items, twoCol = false) {
  return `<ul class="modern-list${twoCol ? ' source-transfer-list-two-col' : ''}">${items.map((item) => `<li><span>${item}</span></li>`).join('')}</ul>`;
}

function accordion(items) {
  return `<div class="accordion reveal">${items.map((item) => `<div class="accordion-item">
<button class="accordion-header">${item.q}<svg class="accordion-icon" fill="none" height="24" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><polyline points="6 9 12 15 18 9"></polyline></svg></button>
<div class="accordion-content"><div class="accordion-body"><p>${item.a}</p></div></div>
</div>`).join('\n')}</div>`;
}

function legalTextMain(content) {
  return `<main>
<section class="section legal-content ludwig-legal-section">
<div class="container container-narrow legal-copy ludwig-legal-copy">
${content}
</div>
</section>
</main>`;
}

function schadenfallMain() {
  return `<main>
<section class="hero hero-small">
<div class="hero-bg"><img alt="Ludwig Oelze Beratung" loading="eager" src="Ludwig_prev_foto/_X8A3007_prev.webp"/></div>
<div class="hero-overlay" style="background: linear-gradient(135deg, rgba(8, 37, 37, 0.88) 0%, rgba(18, 64, 64, 0.78) 48%, rgba(201, 169, 98, 0.46) 100%);"></div>
<div class="hero-content">
<span class="hero-badge">${icon.file} Schadenfall</span>
<h1>Schadenmeldung</h1>
<p class="hero-subtitle">Um deinen Schadensfall schnell und rechtssicher bearbeiten zu k&ouml;nnen, ben&ouml;tige ich die unten abgefragten Daten.</p>
</div>
</section>
<section class="section bg-white">
<div class="container">
<div class="two-col schadenfall-grid">
<div class="reveal">
<h2>Schadenmeldung</h2>
<p>Um deinen Schadensfall schnell und rechtssicher bearbeiten zu k&ouml;nnen, ben&ouml;tige ich die unten abgefragten Daten. So kann ich die Informationen speichern und dem Versicherer vorlegen.</p>
<p>Wichtig: Bitte f&uuml;ge mehrere PDF-Dokumente zusammen und konvertiere alle Fotos oder Bilder in eine PDF-Datei. Nutze daf&uuml;r die folgenden Links:</p>
<ul class="link-list">
<li><a href="https://www.ilovepdf.com/de/pdfs-zusammenfuegen" rel="noopener noreferrer" target="_blank">PDFs zusammenf&uuml;gen</a></li>
<li><a href="https://www.ilovepdf.com/de/jpg_zu_pdf" rel="noopener noreferrer" target="_blank">Fotos/Bilder in PDF umwandeln</a></li>
<li><a href="https://www.ilovepdf.com/de/pdf_komprimieren" rel="noopener noreferrer" target="_blank">PDF-Dateigr&ouml;&szlig;e verkleinern</a></li>
</ul>
<p>Bitte f&uuml;ge alle relevanten Informationen bei. Bilder, Anschaffungsrechnungen oder Kostenvoranschl&auml;ge sind zwingend bei Sachsch&auml;den. Arztberichte sind zwingend bei Personensch&auml;den. Ganz unten k&ouml;nnen mehrere PDF-Dateien hochgeladen werden.</p>
<p>Achtung: Im Chrome Browser f&uuml;r Android kann es sein, dass du keine PDF-Dateien f&uuml;r den Upload ausw&auml;hlen kannst. Nutze dann bitte den Firefox-Browser <a href="https://play.google.com/store/apps/details?id=org.mozilla.firefox" rel="noopener noreferrer" target="_blank">HIER</a></p>
</div>
<div class="card schadenfall-form-card reveal reveal-right">
<form action="#" method="POST" enctype="multipart/form-data" data-validate data-form-purpose="schadenfall">
<input type="hidden" name="recipient" value="finanzen@ludwigoelze.com"/>
<div class="form-group">
<label class="form-label" for="schadenfall-name">Name Versicherungsnehmer*</label>
<input class="form-input" id="schadenfall-name" name="name_versicherungsnehmer" placeholder="Name Versicherungsnehmer*" required type="text"/>
</div>
<div class="form-group">
<label class="form-label" for="schadenfall-email">E-Mail*</label>
<input class="form-input" id="schadenfall-email" name="email" placeholder="E-Mail*" required type="email"/>
</div>
<div class="form-group">
<label class="form-label" for="schadenfall-date-place">Datum, Uhrzeit und Ort des Schadens*</label>
<textarea class="form-textarea" id="schadenfall-date-place" name="datum_uhrzeit_ort" placeholder="Datum, Uhrzeit und Ort (genaue Adresse) des Schadens*" required rows="4"></textarea>
</div>
<div class="form-group">
<label class="form-label" for="schadenfall-description">Genaue Schilderung*</label>
<textarea class="form-textarea" id="schadenfall-description" name="genaue_schilderung" placeholder="Genaue Schilderung, was passiert ist (3-10 S&auml;tze) und die Ursache (z.B. Rohrbruch)*" required rows="4"></textarea>
</div>
<div class="form-group">
<label class="form-label" for="schadenfall-involved">Gesch&auml;digte, Beteiligte und Zeugen*</label>
<textarea class="form-textarea" id="schadenfall-involved" name="geschaedigte_beteiligte_zeugen" placeholder="Name, Adresse, Telefonnummer &amp; E-Mail des Gesch&auml;digten und weiteren Beteiligten Personen/Zeugen.*" required rows="4"></textarea>
</div>
<div class="form-group">
<label class="form-label" for="schadenfall-police">Polizeiliche Meldung</label>
<textarea class="form-textarea" id="schadenfall-police" name="polizeiliche_meldung" placeholder="Bei polizeilicher Meldung (zwingend bei Diebstahl) das Aktenzeichen und die Kontaktstelle der Polizei." rows="4"></textarea>
</div>
<div class="form-group">
<label class="form-label" for="schadenfall-damaged-item">Besch&auml;digter Gegenstand oder Verletzung*</label>
<textarea class="form-textarea" id="schadenfall-damaged-item" name="beschaedigter_gegenstand_oder_verletzung" placeholder="Beschreibung des besch&auml;digten Gegenstands (z.B. iPhone 16 - 128GB) oder der Verletzung.*" required rows="4"></textarea>
</div>
<div class="form-group">
<label class="form-label" for="schadenfall-amount">Gesch&auml;tzte Schadenh&ouml;he in &euro;</label>
<input class="form-input" id="schadenfall-amount" name="geschaetzte_schadenhoehe" inputmode="decimal" min="0" placeholder="Gesch&auml;tzte Schadenh&ouml;he in &euro;" step="0.01" type="number"/>
</div>
<div class="form-group">
<label class="form-label" for="schadenfall-vehicle">Bei KFZ Sch&auml;den</label>
<textarea class="form-textarea" id="schadenfall-vehicle" name="kfz_schaeden" placeholder="Bei KFZ Sch&auml;den: Marke, Modell, Kennzeichen der gesch&auml;digten Fahrzeuge." rows="4"></textarea>
</div>
<div class="form-group">
<label class="form-label" for="schadenfall-files">PDF-Datei hochladen</label>
<input accept="application/pdf,.pdf" class="form-input form-input-file" id="schadenfall-files" name="attachments[]" multiple type="file"/>
</div>
<button class="btn btn-primary btn-full" type="submit">Senden</button>
</form>
</div>
</div>
</div>
</section>
</main>`;
}

function healthTrustGrid(items) {
  return `<div class="grid grid-4">
${items.map(([title, text, iconSvg], index) => card(title, text, iconSvg, `stagger-${(index % 6) + 1}`)).join('\n')}
</div>`;
}

function healthSplit(heading, paragraphs, asideTitle, bullets, bg = 'bg-white') {
  return `<section class="section ${bg}">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>${heading}</h2>
${paragraphs.map((paragraph) => `<p>${paragraph}</p>`).join('\n')}
</div>
<div class="card reveal reveal-right">
<h3>${asideTitle}</h3>
${list(bullets, bullets.length > 4)}
</div>
</div>
</div>
</section>`;
}

function healthReviewsSection() {
  return `<section class="section bg-white" id="bewertungen">
<div class="container">
<div class="section-header reveal">
<span class="section-badge">Bewertungen</span>
<h2>Bewertungen</h2>
</div>
<div class="card reviews-embed-card reveal">
<div class="reviews-embed-inner">
<script src="https://static.elfsight.com/platform/platform.js" async></script>
<div class="elfsight-app-da863271-a986-48a3-9a7b-fc42d45556b9" data-elfsight-app-lazy></div>
</div>
</div>
</div>
</section>`;
}

function healthTestimonials(items) {
  return `<div class="grid grid-3">
${items.map(([quote, name], index) => `<div class="card reveal stagger-${index + 1}">
<p>&ldquo;${quote}&rdquo;</p>
<strong>${name}</strong>
</div>`).join('\n')}
</div>`;
}

function expatHealthPageMain(config) {
  return `<main>
<section class="hero hero-small">
<div class="hero-overlay" style="background: var(--gradient-hero);"></div>
<div class="hero-shapes">
<div class="hero-shape hero-shape-1"></div>
<div class="hero-shape hero-shape-2"></div>
</div>
<div class="hero-content">
<span class="hero-badge">${config.badgeIcon} ${config.badge}</span>
<h1>${config.title}</h1>
<p class="hero-subtitle">${config.subtitle}</p>
</div>
</section>
<section class="section bg-white">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>${config.introHeading}</h2>
${config.intro.map((paragraph) => `<p>${paragraph}</p>`).join('\n')}
</div>
<div class="card reveal reveal-right">
<h3>${config.specializationHeading}</h3>
${config.specialization.map((paragraph) => `<p>${paragraph}</p>`).join('\n')}
</div>
</div>
</div>
</section>
<section class="section bg-off-white">
<div class="container">
<div class="section-header reveal">
<h2>Warum du mir vertrauen kannst</h2>
</div>
${healthTrustGrid(config.trust)}
</div>
</section>
<section class="section bg-white">
<div class="container">
<div class="section-header reveal">
<h2>So helfe ich dir konkret</h2>
</div>
<div class="grid grid-3">
<div class="card reveal stagger-1"><h3>${config.forTitle}</h3>${list(config.forItems, true)}</div>
<div class="card reveal stagger-2"><h3>Ich helfe dir bei:</h3>${list(config.helpItems, true)}</div>
<div class="card reveal stagger-3"><h3>${config.expertiseTitle}</h3>${list(config.expertiseItems, true)}</div>
</div>
</div>
</section>
${healthSplit(config.whyHeading, config.whyParagraphs, config.questionTitle, config.questionBullets, 'bg-off-white')}
<section class="section bg-white">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>${config.promiseHeading}</h2>
${config.promiseParagraphs.map((paragraph) => `<p>${paragraph}</p>`).join('\n')}
</div>
<div class="card reveal reveal-right">
<h3>${config.ctaCardTitle}</h3>
<p>${config.ctaCardText}</p>
<a class="btn btn-primary btn-full" href="${config.whatsapp}" rel="noopener noreferrer" target="_blank">Stelle eine Frage per WhatsApp</a>
</div>
</div>
</div>
</section>
${config.reviews ? healthReviewsSection() : ''}
<section class="section bg-off-white">
<div class="container container-narrow">
<div class="section-header reveal"><h2>H&auml;ufige Fragen</h2></div>
${accordion(config.faq)}
</div>
</section>
<section class="section bg-white">
<div class="container">
<div class="section-header reveal">
<h2>${config.testimonialHeading}</h2>
</div>
${healthTestimonials(config.testimonials)}
</div>
</section>
<section class="cta-section" id="contact">
<div class="container">
<h2 class="reveal">${config.finalHeading}</h2>
<p class="reveal">${config.finalText}</p>
<div class="cta-actions">
<a class="btn btn-primary btn-lg" href="${calendlyLinks.beratung}" rel="noopener noreferrer" target="_blank">Kostenlose Beratung vereinbaren</a>
<a class="btn btn-secondary btn-lg" href="${config.whatsapp}" rel="noopener noreferrer" target="_blank">WhatsApp senden</a>
</div>
</div>
</section>
</main>`;
}

function freelancerNomadenMain() {
  return expatHealthPageMain({
    badgeIcon: icon.globe,
    badge: 'Dein Nomaden-Experte zwischen Spanien &amp; Dubai',
    title: 'Krankenversicherung f&uuml;r <span class="highlight">Selbst&auml;ndige in Spanien</span> mit Dubai-Aufenthalten',
    subtitle: 'Spezialisierte Beratung f&uuml;r digitale Nomaden, Freelancer und mobile Unternehmer.',
    introHeading: 'Ich bin Ludwig Oelze - dein Nomaden-Experte',
    intro: ['Als unabh&auml;ngiger Versicherungsmakler habe ich einen klaren Fokus: Krankenversicherung f&uuml;r deutsche Selbst&auml;ndige und Freelancer, die zwischen Spanien und Dubai pendeln.'],
    specializationHeading: 'Meine Spezialisierung f&uuml;r dich',
    specialization: ['Als digitaler Nomade kenne ich die Herausforderungen des mobilen Lebens. Deshalb habe ich mich auf Krankenversicherungen f&uuml;r deutsche Selbst&auml;ndige spezialisiert, die haupts&auml;chlich in Spanien leben, aber regelm&auml;&szlig;ig in Dubai arbeiten oder sich aufhalten.', 'Dein mobiler Lifestyle braucht keine Standard-L&ouml;sungen, sondern jemanden, der versteht, wie Selbst&auml;ndigkeit, Residenz und internationale Aufenthalte zusammenspielen.'],
    trust: [['Spanien-Expertise', 'Ich kenne die spanischen Residenz-Anforderungen und Gesundheitssystem-Besonderheiten.', icon.globe], ['Dubai-Erfahrung', 'Praktische Erfahrung mit UAE-Visa-Anforderungen und lokalen Gesundheitssystemen.', icon.card], ['Selbst&auml;ndigen-Fokus', 'Speziell auf die Bed&uuml;rfnisse von Freelancern und digitalen Nomaden ausgerichtet.', icon.file], ['Unabh&auml;ngig &amp; ehrlich', 'Ich sage dir auch, was du NICHT brauchst - dein Vorteil steht im Mittelpunkt.', icon.check]],
    forTitle: 'Perfekt f&uuml;r:',
    forItems: ['Deutsche Selbst&auml;ndige mit Residenz in Spanien', 'Digitale Nomaden zwischen Europa und Dubai', 'Freelancer mit Kunden in beiden L&auml;ndern', 'Online-Unternehmer mit Dubai-Aufenthalten', 'Consultants mit internationalen Projekten'],
    helpItems: ['EU-konforme Krankenversicherung f&uuml;r Spanien-Residenten', 'Zusatzschutz f&uuml;r Dubai und VAE-Aufenthalte', 'Internationale Versicherungsl&ouml;sungen f&uuml;r Nomaden', 'Steueroptimierte Versicherungsgestaltung', 'Nahtloser Schutz bei L&auml;nder-Wechseln'],
    expertiseTitle: 'Meine Nomaden-Expertise:',
    expertiseItems: ['Spanische Gesundheitssystem-Regulierungen', 'UAE/Dubai Visa-konforme Versicherungen', 'Internationale Krankenversicherungen f&uuml;r Selbst&auml;ndige', 'Steuerliche Absetzbarkeit in beiden L&auml;ndern', '24/7 weltweite Notfall-Assistance'],
    whyHeading: 'Warum diese spezielle Kombination?',
    whyParagraphs: ['Spanien ist f&uuml;r deutsche Selbst&auml;ndige steuerlich attraktiv, Dubai bietet unglaubliche Business-M&ouml;glichkeiten. Aber die Krankenversicherung zwischen beiden L&auml;ndern ist kompliziert und wird oft falsch gemacht.', 'Viele Nomaden haben L&uuml;cken im Versicherungsschutz, zahlen doppelt oder erf&uuml;llen nicht die Residenz-Anforderungen. Ich kenne beide Systeme und sorge daf&uuml;r, dass du optimal und rechtssicher abgesichert bist.', 'Als jemand, der selbst international arbeitet, verstehe ich deine Herausforderungen: Visa-Anforderungen, Steuerpflicht, Behandlungskosten und die Balance zwischen Flexibilit&auml;t und Sicherheit.'],
    questionTitle: 'Nomaden-Frage zwischen Spanien &amp; Dubai?',
    questionBullets: ['Nomaden-Experte antwortet pers&ouml;nlich', 'Spanien &amp; Dubai Expertise', 'Kostenlos &amp; unverbindlich'],
    promiseHeading: 'Transparenz und ma&szlig;geschneiderte L&ouml;sungen',
    promiseParagraphs: ['Ich erkl&auml;re dir alle deine M&ouml;glichkeiten zwischen Spanien und Dubai. Ich sage dir auch, was du NICHT brauchst. Ich helfe dir, die richtige Absicherung f&uuml;r deinen mobilen Lifestyle zu finden - einfach, klar und ohne Verkaufsdruck.', 'Deine Freiheit als digitaler Nomade beginnt mit der richtigen Absicherung. Und die findest du nur durch ehrliche, spezialisierte Beratung.'],
    ctaCardTitle: 'Stelle deine Frage direkt',
    ctaCardText: 'Mobiles Leben bedeutet schnelle Antworten zu brauchen. Stelle mir deine Versicherungsfrage direkt per WhatsApp.',
    whatsapp: 'https://wa.me/4917643689181?text=Hallo%20Ludwig,%20ich%20bin%20Selbst%C3%A4ndige/r%20zwischen%20Spanien%20und%20Dubai%20und%20habe%20eine%20Frage%20zur%20Krankenversicherung...',
    reviews: true,
    faq: [
      { q: 'Kann ich als deutscher Selbst&auml;ndiger mit Residenz in Spanien einfach nach Dubai reisen?', a: 'Ja, aber du brauchst den richtigen Versicherungsschutz. Als Spanien-Resident hast du &uuml;ber die Europ&auml;ische Krankenversicherungskarte Grundschutz in der EU, aber nicht in Dubai. F&uuml;r UAE-Aufenthalte brauchst du zus&auml;tzlichen internationalen Schutz oder eine spezielle Reiseversicherung.' },
      { q: 'Welche Krankenversicherung brauche ich als Selbst&auml;ndiger in Spanien mit Dubai-Aufenthalten?', a: 'Das h&auml;ngt von Aufenthaltsdauer und Status ab: spanische Residenz, Dubai-Aufenthalte unter 90 Tagen, l&auml;ngere Dubai-Aufenthalte oder parallele Dubai-Residenz brauchen unterschiedliche Kombinationen.' },
      { q: 'Kann ich meine deutsche Krankenversicherung als Spanien-Resident behalten?', a: 'Wenn du deinen Lebensmittelpunkt nach Spanien verlegst, endet normalerweise die deutsche Krankenversicherungspflicht. Eine freiwillige Weiterversicherung ist oft teuer und bietet nicht automatisch Schutz in Spanien. Meist ist eine spanische oder internationale L&ouml;sung sinnvoller.' },
      { q: 'Was kostet eine gute Krankenversicherung f&uuml;r Nomaden zwischen Spanien und Dubai?', a: 'Die Kosten variieren je nach Abdeckung: spanische Basisversicherung, internationale Reiseversicherung, weltweite Krankenversicherung oder Premium-Nomaden-Tarife. Ich finde die kosteneffizienteste L&ouml;sung f&uuml;r dein Reise- und Arbeitsverhalten.' },
      { q: 'Was passiert bei einem medizinischen Notfall in Dubai ohne passende Versicherung?', a: 'Dubai hat eines der teuersten Gesundheitssysteme weltweit. Ein Arztbesuch oder Krankenhausaufenthalt kann sehr teuer werden; ohne Versicherung musst du alles selbst zahlen.' },
    ],
    testimonialHeading: 'Erfahrungen',
    testimonials: [['Ludwig hat die perfekte L&ouml;sung f&uuml;r mein Leben zwischen Valencia und Dubai gefunden. Endlich keine Sorgen mehr bei Grenz&uuml;bertritten!', '- Marcus K., Freelancer seit 2022 in Spanien'], ['Als digitale Nomadin brauchte ich flexiblen Schutz. Ludwig versteht den Lifestyle und hat mir viel Geld gespart!', '- Sarah M., Online-Marketing Consultantin'], ['Komplizierte Steuer- und Versicherungsfragen einfach erkl&auml;rt. Jetzt bin ich optimal abgesichert zwischen beiden L&auml;ndern.', '- David R., E-Commerce Unternehmer']],
    finalHeading: 'Starte jetzt deine optimale Nomaden-Absicherung',
    finalText: 'Sichere dir dein kostenloses Orientierungsgespr&auml;ch und erfahre, wie du dich zwischen Spanien und Dubai optimal und kosteng&uuml;nstig absichern kannst.',
  });
}

function immobilienNomadenMain() {
  return expatHealthPageMain({
    badgeIcon: icon.globe,
    badge: 'Dein internationaler Experte',
    title: 'Krankenversicherung f&uuml;r <span class="highlight">Immobilien-Unternehmer</span>',
    subtitle: 'Spezialisierte Beratung f&uuml;r Unternehmer mit Immobilien in Deutschland, Spanien &amp; Dubai.',
    introHeading: 'Ich bin Ludwig Oelze - dein internationaler Experte',
    intro: ['Als unabh&auml;ngiger Versicherungsmakler mit internationaler Erfahrung habe ich einen klaren Fokus: Krankenversicherung f&uuml;r Unternehmer mit Immobilien in Deutschland, Spanien und Dubai.'],
    specializationHeading: 'Meine Spezialisierung f&uuml;r dich',
    specialization: ['Nach vielen Jahren als Allfinanzberater und eigenen Erfahrungen als Multi-Location-Unternehmer verstehe ich die komplexen Herausforderungen der Krankenversicherung bei Immobiliengesch&auml;ften in mehreren L&auml;ndern.', 'Als Immobilien-Unternehmer brauchst du keinen Alles-Anbieter, sondern jemanden, der die spezifischen Anforderungen deiner internationalen Gesch&auml;ftst&auml;tigkeit versteht und ehrlich ber&auml;t.'],
    trust: [['Multi-Location-Expertise', 'Ich kenne die Versicherungsanforderungen in Deutschland, Spanien und Dubai aus eigener Erfahrung.', icon.globe], ['Immobilien-Spezialist', 'Fokus auf Unternehmer mit Immobilienportfolios - keine Ablenkung durch andere Branchen.', icon.file], ['Unabh&auml;ngig &amp; ehrlich', 'Ich sage dir auch, was du NICHT brauchst - dein Erfolg steht im Mittelpunkt.', icon.check], ['Rechtssichere Beratung', 'Ich kenne steuerliche und rechtliche Besonderheiten in allen drei L&auml;ndern.', icon.shield]],
    forTitle: 'Deutschland-Expertise:',
    forItems: ['GKV vs. PKV f&uuml;r Immobilien-Unternehmer', 'Auslandsaufenthalte und Meldepflichten', 'Steuerliche Absetzbarkeit optimieren', 'Familienversicherung bei internationaler T&auml;tigkeit', 'R&uuml;ckkehr-Szenarien absichern'],
    helpItems: ['Residencia-konforme Krankenversicherung', 'Autonomo und Sociedades-Regelungen', 'Balearische und festl&auml;ndische Besonderheiten', 'EU-Krankenversicherungskarte vs. private Absicherung', 'Steueroptimierte Versicherungsl&ouml;sungen'],
    expertiseTitle: 'Dubai/VAE-Expertise:',
    expertiseItems: ['Visa-konforme Krankenversicherung f&uuml;r Investoren', 'Lokale vs. internationale Anbieter', 'Freezone-spezifische Anforderungen', 'Immobilien-Investor-Visa Absicherung', 'Steuerfreie Strukturierung'],
    whyHeading: 'Warum nur Immobilien-Unternehmer?',
    whyParagraphs: ['Immobilien-Unternehmer mit internationalen Aktivit&auml;ten haben einzigartige Herausforderungen: Wohnsitzwechsel, unterschiedliche Steuersysteme, Visa-Anforderungen und komplexe Unternehmensstrukturen.', 'Heute konzentriere ich mich auf Krankenversicherung f&uuml;r Immobilien-Unternehmer mit Multi-Location-Gesch&auml;ften, weil Spezialisierung mehr bringt als Oberfl&auml;che.', 'Als Unternehmer mit eigenen internationalen Immobilienaktivit&auml;ten kenne ich deine Herausforderungen aus erster Hand: Steueroptimierung, Rechtssicherheit und die Balance zwischen Kosten und optimaler Absicherung.'],
    questionTitle: 'Komplexe Frage als Immobilien-Unternehmer?',
    questionBullets: ['Multi-Location-Experte antwortet pers&ouml;nlich', 'Steueroptimierte Beratung', 'Kostenlos &amp; unverbindlich'],
    promiseHeading: 'Transparenz und Expertise - immer',
    promiseParagraphs: ['Ich erkl&auml;re dir alle deine M&ouml;glichkeiten in Deutschland, Spanien und Dubai. Ich sage dir auch, was du NICHT brauchst. Ich helfe dir, die steuerlich und rechtlich optimale Absicherung zu finden - einfach, klar und ohne Verkaufsdruck.', 'Dein internationaler Immobilien-Erfolg beginnt mit der richtigen Absicherung. Und die findest du nur durch ehrliche, spezialisierte Beratung.'],
    ctaCardTitle: 'Stelle deine Frage direkt',
    ctaCardText: 'Multi-Location-Gesch&auml;fte bedeuten komplexe Versicherungsfragen. Stelle mir deine spezifische Frage direkt per WhatsApp.',
    whatsapp: 'https://wa.me/4917643689181?text=Hallo%20Ludwig,%20ich%20bin%20Immobilien-Unternehmer%20und%20habe%20eine%20Frage%20zur%20Krankenversicherung%20in%20Deutschland/Spanien/Dubai...',
    faq: [
      { q: 'Wie funktioniert die Krankenversicherung als Immobilien-Unternehmer mit Wohnsitzen in mehreren L&auml;ndern?', a: 'Das h&auml;ngt vom Hauptwohnsitz, der Dauer deiner Aufenthalte und deinen Unternehmensstrukturen ab. Deutschland, Spanien und Dubai stellen jeweils eigene Anforderungen.' },
      { q: 'Kann ich meine deutsche Krankenversicherung in Spanien und Dubai nutzen?', a: 'Teilweise, aber es gibt wichtige Einschr&auml;nkungen. Die EU-Krankenversicherungskarte hilft nicht bei allen Residencia-Anforderungen und deutsche Versicherungen werden in Dubai nicht automatisch als visa-konform anerkannt.' },
      { q: 'Welche Krankenversicherung brauche ich f&uuml;r mein Investor-Visa in Dubai?', a: 'F&uuml;r Immobilien-Investor-Visas gelten spezielle Anforderungen wie Mindestdeckung, zugelassene Anbieter und G&uuml;ltigkeit &uuml;ber die Visa-Laufzeit.' },
      { q: 'Was passiert bei einem medizinischen Notfall im Ausland?', a: 'Das h&auml;ngt von deiner Struktur ab: lokale Versicherungen, internationale Versicherungen, Reiseversicherungen und R&uuml;cktransport m&uuml;ssen sauber zusammenspielen.' },
      { q: 'Wie teuer ist eine optimale Krankenversicherung f&uuml;r Multi-Location-Unternehmer?', a: 'Die Kosten variieren stark je nach Umfang und L&auml;ndern. Ich ber&uuml;cksichtige Basis-Absicherung, internationale Premium-Tarife, Kombinations-L&ouml;sungen und steuerliche Vorteile.' },
    ],
    testimonialHeading: 'Erfahrungen',
    testimonials: [['Ludwig hat f&uuml;r mein Immobilien-Portfolio in allen drei L&auml;ndern die perfekte Versicherungsl&ouml;sung gefunden. Steueroptimiert und rechtssicher!', '- Marcus K., Immobilien-Investor'], ['Endlich jemand, der die komplexen Anforderungen von Multi-Location-Unternehmern versteht. Danke f&uuml;r die transparente Beratung!', '- Andrea M., Immobilien-Unternehmerin'], ['Die Beratung hat mir nicht nur Geld gespart, sondern auch rechtliche Sicherheit in allen drei L&auml;ndern gegeben. Top Expertise!', '- Stefan R., Dubai-Investor']],
    finalHeading: 'Starte jetzt deine optimale internationale Absicherung',
    finalText: 'Sichere dir dein kostenloses Strategiegespr&auml;ch und erfahre, wie du dich als Immobilien-Unternehmer in Deutschland, Spanien und Dubai optimal und steueroptimiert absichern kannst.',
  });
}

function digitaleNomadenMain() {
  return expatHealthPageMain({
    badgeIcon: icon.globe,
    badge: 'Dein internationaler Experte',
    title: 'Krankenversicherung f&uuml;r deutsche <span class="highlight">digitale Nomaden</span>',
    subtitle: 'Spezialisierte Beratung f&uuml;r deine optimale Gesundheitsabsicherung in Spanien &amp; Dubai.',
    introHeading: 'Ich bin Ludwig Oelze - dein internationaler Experte',
    intro: ['Als unabh&auml;ngiger Versicherungsmakler mit internationaler Erfahrung habe ich einen klaren Fokus: Krankenversicherung f&uuml;r deutsche digitale Nomaden, die in Spanien und Dubai arbeiten.'],
    specializationHeading: 'Meine Spezialisierung f&uuml;r dich',
    specialization: ['Nach vielen Jahren als Allfinanzberater in Deutschland und eigener Erfahrung als digitaler Nomade habe ich mich auf Krankenversicherungen f&uuml;r deutsche Remote Worker in Spanien und Dubai fokussiert.', 'Als digitaler Nomade brauchst du keinen Alles-Anbieter, sondern jemanden, der die Herausforderungen des ortsunabh&auml;ngigen Arbeitens versteht und ehrlich ber&auml;t.'],
    trust: [['Internationale Erfahrung', 'Ich kenne die Herausforderungen digitaler Nomaden in Spanien und Dubai aus eigener Erfahrung.', icon.globe], ['100% spezialisiert', 'Nur Krankenversicherungen f&uuml;r digitale Nomaden - keine Ablenkung durch andere Produkte.', icon.shield], ['Unabh&auml;ngig &amp; ehrlich', 'Ich sage dir auch, was du NICHT brauchst - dein Vorteil steht im Mittelpunkt.', icon.check], ['Deutsche Gr&uuml;ndlichkeit', 'Transparente Beratung auf Deutsch mit dem Service, den du gewohnt bist.', icon.file]],
    forTitle: 'Individuelle Beratung f&uuml;r:',
    forItems: ['Freelancer &amp; Selbstst&auml;ndige in Spanien', 'Remote Worker &amp; digitale Nomaden in Dubai', 'Online-Unternehmer mit Wohnsitz im Ausland', 'Deutsche mit Residencia in Spanien', 'Tempor&auml;re Aufenthalte &amp; Visa-Wechsel'],
    helpItems: ['Internationaler Krankenversicherung f&uuml;r Nomaden', 'Spanien-konformer Gesundheitsabsicherung', 'Dubai-tauglichen Versicherungsl&ouml;sungen', 'Flexiblen Tarifen f&uuml;r ortsunabh&auml;ngiges Arbeiten', 'Optimierung deiner bestehenden Absicherung'],
    expertiseTitle: 'Meine Nomaden-Expertise:',
    expertiseItems: ['Spanische Gesundheitssystem-Anforderungen', 'Dubai/UAE Visa-konforme Versicherungen', 'Internationale Krankenversicherungen mit weltweiter Deckung', 'Flexible Tarife f&uuml;r wechselnde Aufenthaltsorte', 'Deutschsprachiger Support weltweit'],
    whyHeading: 'Warum nur Krankenversicherung f&uuml;r Nomaden?',
    whyParagraphs: ['Der Versicherungsmarkt f&uuml;r digitale Nomaden ist komplex: verschiedene L&auml;nder, unterschiedliche Regulierungen, aber wenig Klarheit f&uuml;r deutsche Remote Worker.', 'Heute konzentriere ich mich auf Krankenversicherung f&uuml;r deutsche digitale Nomaden, weil Spezialisierung mehr bringt als Oberfl&auml;che.', 'Als jemand, der selbst ortsunabh&auml;ngig arbeitet, kenne ich deine Herausforderungen: Visa-Bestimmungen, lokale &Auml;rzte, Behandlungskosten und die Balance zwischen Flexibilit&auml;t und Sicherheit.'],
    questionTitle: 'Schnelle Frage als digitaler Nomade?',
    questionBullets: ['Nomaden-Experte antwortet pers&ouml;nlich', 'Keine Wartezeit', 'Kostenlos &amp; unverbindlich'],
    promiseHeading: 'Transparenz und Ehrlichkeit - immer',
    promiseParagraphs: ['Ich erkl&auml;re dir alle deine M&ouml;glichkeiten als digitaler Nomade. Ich sage dir auch, was du NICHT brauchst. Ich helfe dir, die richtige Absicherung zu finden - flexibel, klar und ohne Verkaufsdruck.', 'Deine Freiheit als digitaler Nomade beginnt mit der richtigen Absicherung. Und die findest du nur durch ehrliche Beratung.'],
    ctaCardTitle: 'Stelle deine Frage direkt',
    ctaCardText: 'Ortsunabh&auml;ngig arbeiten bedeutet auch schnelle Antworten zu brauchen. Stelle mir deine Versicherungsfrage direkt per WhatsApp.',
    whatsapp: 'https://wa.me/4917643689181?text=Hallo%20Ludwig,%20ich%20bin%20digitaler%20Nomade%20und%20habe%20eine%20Frage%20zur%20Krankenversicherung%20in%20Spanien/Dubai...',
    faq: [
      { q: 'Brauche ich als deutscher digitaler Nomade in Spanien eine spezielle Krankenversicherung?', a: 'Ja, wenn du l&auml;nger als 90 Tage in Spanien bleibst oder dort deinen Wohnsitz anmeldest, brauchst du eine Krankenversicherung, die in Spanien anerkannt wird.' },
      { q: 'Was ist die beste Krankenversicherung f&uuml;r digitale Nomaden zwischen Spanien und Dubai?', a: 'Es gibt internationale Krankenversicherungen, nomadenspezifische Versicherungen, kombinierte L&ouml;sungen und regionale Anpassungen. Die beste L&ouml;sung h&auml;ngt von Reisemuster und Budget ab.' },
      { q: 'Kann ich meine deutsche Krankenversicherung als digitaler Nomade behalten?', a: 'Das h&auml;ngt von deiner Situation ab. Gesetzliche Krankenversicherung, private Krankenversicherung und Anwartschaftsversicherung m&uuml;ssen jeweils konkret gepr&uuml;ft werden.' },
      { q: 'Was kostet eine gute Krankenversicherung f&uuml;r digitale Nomaden?', a: 'Je nach Alter, Gesundheitszustand und Leistungsumfang reichen die Kosten von Basis-Nomaden-Versicherungen bis zu umfassenden internationalen Premium-Tarifen.' },
      { q: 'Wie schnell kann ich als digitaler Nomade eine Krankenversicherung abschlie&szlig;en?', a: 'Bei vielen internationalen Anbietern ist ein Abschluss innerhalb von 1-5 Werktagen m&ouml;glich; manche bieten sogar Sofortschutz ab Antragstellung.' },
    ],
    testimonialHeading: 'Erfahrungen',
    testimonials: [['Ludwig hat mir als digitaler Nomade die perfekte Versicherung f&uuml;r Spanien und Dubai gefunden. Flexibel, bezahlbar und zuverl&auml;ssig!', '- Marcus K., Remote Developer'], ['Endlich jemand, der die Nomaden-Realit&auml;t versteht! Transparente Beratung ohne Verkaufsdruck.', '- Lisa M., Online-Unternehmerin'], ['Als Freelancer zwischen Valencia und Dubai war ich v&ouml;llig &uuml;berfordert. Ludwig hat mir die perfekte, flexible L&ouml;sung gefunden!', '- Stefan R., Freelancer &amp; Nomade']],
    finalHeading: 'Starte jetzt deine optimale Nomaden-Absicherung',
    finalText: 'Sichere dir dein kostenloses Orientierungsgespr&auml;ch und erfahre, wie du dich als digitaler Nomade optimal und flexibel absichern kannst.',
  });
}

function expatBeratungMain() {
  return expatHealthPageMain({
    badgeIcon: icon.globe,
    badge: 'Dein Experte in Dubai',
    title: 'Krankenversicherung f&uuml;r deutsche <span class="highlight">Expats in Dubai</span>',
    subtitle: 'Spezialisierte Beratung f&uuml;r deine optimale Gesundheitsabsicherung in den VAE.',
    introHeading: 'Ich bin Ludwig Oelze - dein Experte vor Ort',
    intro: ['Als unabh&auml;ngiger Versicherungsmakler in Dubai habe ich einen klaren Fokus: Krankenversicherung f&uuml;r deutschsprachige Expats in Dubai und den VAE.'],
    specializationHeading: 'Meine Spezialisierung f&uuml;r dich',
    specialization: ['Nach vielen Jahren als Allfinanzberater in Deutschland lebe ich jetzt in Dubai und habe mich auf Krankenversicherungen f&uuml;r deutsche Expats in Dubai fokussiert. Lokale Expertise trifft deutsche Gr&uuml;ndlichkeit.', 'In einem Markt voller Komplexit&auml;t brauchst du keinen Alles-Anbieter, sondern jemanden, der sich auskennt, vor Ort ist und ehrlich ber&auml;t.'],
    trust: [['Vor Ort in Dubai', 'Ich lebe selbst in Dubai und kenne die lokalen Gegebenheiten aus erster Hand.', icon.globe], ['100% spezialisiert', 'Nur Krankenversicherungen f&uuml;r Expats - keine Ablenkung durch andere Produkte.', icon.shield], ['Unabh&auml;ngig &amp; ehrlich', 'Ich sage dir auch, was du NICHT brauchst - dein Vorteil steht im Mittelpunkt.', icon.check], ['Deutsche Gr&uuml;ndlichkeit', 'Transparente Beratung auf Deutsch mit dem Service, den du gewohnt bist.', icon.file]],
    forTitle: 'Individuelle Beratung f&uuml;r:',
    forItems: ['Selbstst&auml;ndige &amp; Unternehmer in Dubai', 'Digitale Nomaden &amp; Remote-Worker', 'Rentner &amp; Familien mit Kindern', 'Angestellte mit Visa-Wechsel', 'Neu-Expats vor dem Umzug'],
    helpItems: ['Gesetzeskonformer Krankenversicherung in Dubai', 'Auswahl zwischen lokalen und internationalen Anbietern', 'Vertragsabschluss und Schadensfallhilfe', 'Wechsel zwischen verschiedenen Versicherungen', 'Optimierung deiner bestehenden Absicherung'],
    expertiseTitle: 'Meine Dubai-Expertise:',
    expertiseItems: ['Lokale UAE-Versicherungsregulierungen (DHA/HAAD)', 'Internationale Krankenversicherungen f&uuml;r Expats', 'Spezielle Expat-Tarife und Gruppenversicherungen', 'Visa-konforme Versicherungsl&ouml;sungen', 'Deutschsprachiger Support vor Ort'],
    whyHeading: 'Warum nur Krankenversicherung?',
    whyParagraphs: ['Der Versicherungsmarkt in Dubai ist speziell: viele Anbieter, komplexe Regulierungen, aber wenig Transparenz f&uuml;r deutsche Expats.', 'Heute konzentriere ich mich auf Krankenversicherung f&uuml;r Expats in Dubai, weil Spezialisierung mehr bringt als Oberfl&auml;che.', 'Als deutschsprachiger Expat in Dubai kenne ich deine Herausforderungen aus erster Hand: Visa-Anforderungen, lokale &Auml;rzte, Behandlungskosten und die Unterschiede zu Deutschland.'],
    questionTitle: 'Hast du eine schnelle Frage?',
    questionBullets: ['Pers&ouml;nliche Antwort garantiert', 'Keine Wartezeit', 'Kostenlos &amp; unverbindlich'],
    promiseHeading: 'Transparenz und Ehrlichkeit - immer',
    promiseParagraphs: ['Ich erkl&auml;re dir alle deine M&ouml;glichkeiten in Dubai. Ich sage dir auch, was du NICHT brauchst. Ich helfe dir, die richtige Absicherung zu finden - einfach, klar und ohne Verkaufsdruck.', 'Deine Sicherheit im Ausland beginnt mit der richtigen Absicherung. Und die findest du nur durch ehrliche Beratung.'],
    ctaCardTitle: 'Stelle deine Frage direkt',
    ctaCardText: 'Manchmal ist ein kurzer Chat effektiver als ein langes Gespr&auml;ch. Stelle mir deine Frage direkt per WhatsApp.',
    whatsapp: 'https://wa.me/4917643689181?text=Hallo%20Ludwig,%20ich%20habe%20eine%20Frage%20zur%20Krankenversicherung%20in%20Dubai...',
    faq: [
      { q: 'Ist eine Krankenversicherung in Dubai wirklich Pflicht f&uuml;r mich als deutschen Expat?', a: 'Ja. In Dubai und den gesamten VAE ist eine Krankenversicherung gesetzlich vorgeschrieben. Ohne g&uuml;ltige Krankenversicherung bekommst du keine Visa-Verl&auml;ngerung.' },
      { q: 'Welche Krankenversicherung ist f&uuml;r mich als deutschen Expat in Dubai am besten?', a: 'Das h&auml;ngt von deiner individuellen Situation ab. Lokale Versicherungen, internationale Versicherungen, deutsche Spezialanbieter und Gruppenversicherungen m&uuml;ssen sauber verglichen werden.' },
      { q: 'Kann ich meine deutsche Krankenversicherung in Dubai weiter nutzen?', a: 'Deutsche gesetzliche Krankenversicherungen bieten in der Regel keinen ausreichenden Schutz in Dubai. Private deutsche Krankenversicherungen haben teilweise internationale Tarife, aber nicht alle erf&uuml;llen lokale Anforderungen.' },
      { q: 'Was kostet eine gute Krankenversicherung in Dubai f&uuml;r mich?', a: 'Die Kosten variieren stark nach Alter, Gesundheitszustand und Leistungsumfang: von einfachen lokalen Grundversicherungen bis zu umfassenden internationalen Premium-Tarifen.' },
      { q: 'Wie schnell kann ich eine Krankenversicherung in Dubai abschlie&szlig;en?', a: 'Bei vielen Anbietern ist ein Abschluss innerhalb von 1-3 Werktagen m&ouml;glich. Ich helfe dir dabei, Unterlagen korrekt einzureichen und den Prozess zu beschleunigen.' },
    ],
    testimonialHeading: 'Erfahrungen',
    testimonials: [['Ludwig hat uns genau die richtige Versicherung f&uuml;r unsere Familie gefunden. Kompetent, ehrlich und versteht die Expat-Situation perfekt!', '- Thomas K., seit 2021 in Dubai'], ['Endlich jemand, der nicht versucht, mir unn&ouml;tige Produkte zu verkaufen. Danke f&uuml;r die transparente Beratung und den lokalen Support!', '- Sandra M., Freelancerin in Dubai'], ['Als Neu-Expat war ich v&ouml;llig &uuml;berfordert. Ludwig hat mir den ganzen Prozess erkl&auml;rt und die perfekte L&ouml;sung gefunden.', '- Michael R., seit 2023 in Dubai']],
    finalHeading: 'Starte jetzt deine optimale Absicherung',
    finalText: 'Sichere dir dein kostenloses Orientierungsgespr&auml;ch und erfahre, wie du dich in Dubai optimal und kosteng&uuml;nstig absichern kannst.',
  });
}

function impressumMain() {
  return legalTextMain(`<h1>Impressum / Kontakt</h1>
<p>Ludwig Oelze: Mehr als nur Finanzberatung</p>
<p>Einzelunternehmer</p>
<p>Ludwig Oelze</p>
<p>Bismarckstra&szlig;e 26</p>
<p>76530 Baden-Baden</p>
<p>Steuernummer: 33069/03006</p>
<p>Keine Umsatzsteuernummer, da berufliche T&auml;tigkeit nicht Umsatzsteuerpflichtig.</p>
<p>E-Mail: Finanzen@ludwigoelze.com</p>
<p>Tel: 0176 436 89181</p>
<p>IBAN: DE71 2013 0400 0060 0128 87</p>
<p>Inhaltlich verantwortlicher Dienstanbieter nach &sect; 5 TMG / &sect; 55 Abs. 2 RStV:<br/>Ludwig Oelze (Anschrift wie oben)</p>
<p>Erlaubnis nach &sect; 34d Abs. 1 Gewerbeordnung (Versicherungsmakler), Erlaubnis nach &sect;34i Abs. 1 S. 1 Gewerbeordnung (Immobiliendarlehensvermittler) Aufsichtsbeh&ouml;rde: Industrie- und Handelskammer Karlsruhe, Lammstra&szlig;e 13-17, 76133 Karlsruhe</p>
<p>Vermittlerregister (<a href="https://www.vermittlerregister.info" rel="noopener noreferrer" target="_blank">www.vermittlerregister.info</a>):</p>
<p>Registrierungs-Nr. D-KQ7W-92T3Y-75 (f&uuml;r &sect; 34d GewO) bzw. D-W-138-5BMK-01 (f&uuml;r &sect;34i GewO</p>
<p>Beschwerdeverfahren via Online Streitbeilegung f&uuml;r Verbraucher (OS): ec.europa.eu/consumers/odr. Wir sind weder verpflichtet noch bereit, an dem Streitschlichtungsverfahren teilzunehmen.</p>
<h2 class="mt-8">Berufsbezeichnung</h2>
<p>Versicherungsmakler mit Erlaubnis nach &sect; 34d Abs. 1 Gewerbeordnung, Bundesrepublik Deutschland</p>
<p>Immobiliendarlehensvermittler mit Erlaubnis nach &sect;34i Abs. 1 S.1 Gewerbeordnung.</p>
<h2 class="mt-8">Zust&auml;ndige Berufskammer</h2>
<p>Industrie- und Handelskammer Karlsruhe, Lammstra&szlig;e 13-17, 76133 Karlsruhe.</p>
<h2 class="mt-8">Berufsrechtliche Regelungen</h2>
<p>&ndash; &sect; 34d Gewerbeordnung (GewO)<br/>&ndash; &sect;&sect; 59 &ndash; 68 Gesetz &uuml;ber den Versicherungsvertrag (VVG)<br/>&ndash; Verordnung &uuml;ber die Versicherungsvermittlung und &ndash; beratung (VersVermV)</p>
<p>Die berufsrechtlichen Regelungen k&ouml;nnen &uuml;ber die vom Bundesministerium der Justiz und von der juris GmbH betriebenen Homepage <a href="https://www.gesetze-im-internet.de" rel="noopener noreferrer" target="_blank">www.gesetze-im-internet.de</a> eingesehen und abgerufen werden.</p>
<h2 class="mt-8">Datenschutz</h2>
<p>Als Diensteanbieter sind wir gem&auml;&szlig; &sect; 7 Abs.1 TMG f&uuml;r eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach &sect;&sect; 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, &uuml;bermittelte oder gespeicherte fremde Informationen zu &uuml;berwachen oder nach Umst&auml;nden zu forschen, die auf eine rechtswidrige T&auml;tigkeit hinweisen.</p>
<p>Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon unber&uuml;hrt. Eine diesbez&uuml;gliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung m&ouml;glich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden wir diese Inhalte umgehend entfernen.</p>
<h2 class="mt-8">Haftung f&uuml;r Links</h2>
<p>Unser Angebot enth&auml;lt Links zu externen Websites Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb k&ouml;nnen wir f&uuml;r diese fremden Inhalte auch keine Gew&auml;hr &uuml;bernehmen. F&uuml;r die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf m&ouml;gliche Rechtsverst&ouml;&szlig;e &uuml;berpr&uuml;ft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar.</p>
<p>Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Links umgehend entfernen.</p>
<h2 class="mt-8">Urheberrecht</h2>
<p>Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht. Die Vervielf&auml;ltigung, Bearbeitung, Verbreitung und jede Art der Verwertung au&szlig;erhalb der Grenzen des Urheberrechtes bed&uuml;rfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind nur f&uuml;r den privaten, nicht kommerziellen Gebrauch gestattet.</p>
<p>Soweit die Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, werden die Urheberrechte Dritter beachtet. Insbesondere werden Inhalte Dritter als solche gekennzeichnet. Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen entsprechenden Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Inhalte umgehend entfernen.</p>`);
}

function datenschutzMain() {
  return legalTextMain(`<h1>Datenschutzerkl&auml;rung</h1>
<p>Die folgenden Hinweise geben einen einfachen &Uuml;berblick dar&uuml;ber, was mit Ihren personenbezogenen Daten passiert, wenn Sie unsere Website besuchen. Personenbezogene Daten sind alle Daten, mit denen Sie pers&ouml;nlich identifiziert werden k&ouml;nnen. Ausf&uuml;hrliche Informationen zum Thema Datenschutz entnehmen Sie unserer unter diesem Text aufgef&uuml;hrten Datenschutzerkl&auml;rung.</p>
<h2 class="mt-8">Datenerfassung auf unserer Website</h2>
<p>Wer ist verantwortlich f&uuml;r die Datenerfassung auf dieser Website? Die Datenverarbeitung auf dieser Website erfolgt durch den Websitebetreiber. Dessen Kontaktdaten k&ouml;nnen Sie dem Impressum dieser Website entnehmen.</p>
<h2 class="mt-8">Wie erfassen wir Ihre Daten?</h2>
<p>Ihre Daten werden zum einen dadurch erhoben, dass Sie uns diese mitteilen. Hierbei kann es sich z.B. um Daten handeln, die Sie in ein Kontaktformular eingeben.</p>
<p>Andere Daten werden automatisch beim Besuch der Website durch unsere IT-Systeme erfasst. Das sind vor allem technische Daten (z.B. Internetbrowser, Betriebssystem oder Uhrzeit des Seitenaufrufs). Die Erfassung dieser Daten erfolgt automatisch, sobald Sie unsere Website betreten.</p>
<h2 class="mt-8">Wof&uuml;r nutzen wir Ihre Daten?</h2>
<p>Ein Teil der Daten wird erhoben, um eine fehlerfreie Bereitstellung der Website zu gew&auml;hrleisten. Andere Daten k&ouml;nnen zur Analyse Ihres Nutzerverhaltens oder zur Kontaktaufnahme verwendet werden.</p>
<h2 class="mt-8">Kooperationspartner</h2>
<p>Dem Kunden ist es bekannt, dass der Vermittler im Rahmen seiner auftragsgem&auml;&szlig; &uuml;bernommenen Aufgaben mit Kooperationspartnern zusammen arbeitet. Aus diesem Grunde wurden die Kooperationspartner bevollm&auml;chtigt. Zum Zwecke der auftragsgem&auml;&szlig;en Umsetzung ist es neben der Bevollm&auml;chtigung ebenfalls erforderlich, dass der Kooperationspartner die Daten des Kunden erh&auml;lt und ebenfalls im Rahmen dieser datenschutzrechtlichen Einwilligungserkl&auml;rung zur Datenverwendung, Weitergabe oder Speicherung berechtigt ist.</p>
<p>Den nachfolgend genannten Kooperationspartnern wird daher die datenschutzrechtliche Einwilligungserkl&auml;rung im Umfang der hiesigen Datenschutzerkl&auml;rung erteilt. Dies gilt insbesondere auch f&uuml;r die sensiblen pers&ouml;nlichen Daten, insbesondere auch die Gesundheitsdaten des Kunden. Der Kunde willigt in die Datenverwendung aufgrund dieser Datenschutzvereinbarung hinsichtlich der nachfolgend genannten Unternehmen ein:</p>
<p>Fonds Finanz Maklerservice GmbH</p>
<p>Der Kunde erkl&auml;rt die Einwilligung der Datenweitergabe an die vorgenannt benannten Unternehmen, sofern dies zur auftragsgem&auml;&szlig;en Erf&uuml;llung des Vermittlers erforderlich ist.</p>
<h2 class="mt-8">Welche Rechte haben Sie bez&uuml;glich Ihrer Daten?</h2>
<p>Sie haben jederzeit das Recht unentgeltlich Auskunft &uuml;ber Herkunft, Empf&auml;nger und Zweck Ihrer gespeicherten personenbezogenen Daten zu erhalten. Sie haben au&szlig;erdem ein Recht, die Berichtigung, Sperrung oder L&ouml;schung dieser Daten zu verlangen. Hierzu sowie zu weiteren Fragen zum Thema Datenschutz k&ouml;nnen Sie sich jederzeit unter der im Impressum angegebenen Adresse an uns wenden. Des Weiteren steht Ihnen ein Beschwerderecht bei der zust&auml;ndigen Aufsichtsbeh&ouml;rde zu.</p>
<h2 class="mt-8">Analyse-Tools und Tools von Drittanbietern</h2>
<p>Beim Besuch unserer Website kann Ihr Surf-Verhalten statistisch ausgewertet werden. Das geschieht vor allem mit Cookies und mit sogenannten Analyseprogrammen. Die Analyse Ihres Surf-Verhaltens erfolgt in der Regel anonym; das Surf-Verhalten kann nicht zu Ihnen zur&uuml;ckverfolgt werden. Sie k&ouml;nnen dieser Analyse widersprechen oder sie durch die Nichtbenutzung bestimmter Tools verhindern. Detaillierte Informationen dazu finden Sie in der folgenden Datenschutzerkl&auml;rung.</p>
<p>Sie k&ouml;nnen dieser Analyse widersprechen. &Uuml;ber die Widerspruchsm&ouml;glichkeiten werden wir Sie in dieser Datenschutzerkl&auml;rung informieren.</p>
<h2 class="mt-8">Allgemeine Hinweise und Pflichtinformationen</h2>
<p>Die Betreiber dieser Seiten nehmen den Schutz Ihrer pers&ouml;nlichen Daten sehr ernst. Wir behandeln Ihre personenbezogenen Daten vertraulich und entsprechend der gesetzlichen Datenschutzvorschriften sowie dieser Datenschutzerkl&auml;rung.</p>
<p>Wenn Sie diese Website benutzen, werden verschiedene personenbezogene Daten erhoben. Personenbezogene Daten sind Daten, mit denen Sie pers&ouml;nlich identifiziert werden k&ouml;nnen. Die vorliegende Datenschutzerkl&auml;rung erl&auml;utert, welche Daten wir erheben und wof&uuml;r wir sie nutzen. Sie erl&auml;utert auch, wie und zu welchem Zweck das geschieht.</p>
<p>Wir weisen darauf hin, dass die Daten&uuml;bertragung im Internet (z.B. bei der Kommunikation per E-Mail) Sicherheitsl&uuml;cken aufweisen kann. Ein l&uuml;ckenloser Schutz der Daten vor dem Zugriff durch Dritte ist nicht m&ouml;glich.</p>
<h2 class="mt-8">Hinweis zur verantwortlichen Stelle</h2>
<p>Die verantwortliche Stelle f&uuml;r die Datenverarbeitung auf dieser Website ist:</p>
<p>Ludwig Oelze<br/>Bismarckstra&szlig;e 26<br/>76530 Baden-Baden<br/>Telefon: +49 176 436 89181<br/>E-Mail: Finanzen@ludwigoelze.com</p>
<p>Verantwortliche Stelle ist die nat&uuml;rliche oder juristische Person, die allein oder gemeinsam mit anderen &uuml;ber die Zwecke und Mittel der Verarbeitung von personenbezogenen Daten (z.B. Namen, E-Mail-Adressen o. &Auml;.) entscheidet.</p>
<h2 class="mt-8">Widerruf Ihrer Einwilligung zur Datenverarbeitung</h2>
<p>Viele Datenverarbeitungsvorg&auml;nge sind nur mit Ihrer ausdr&uuml;cklichen Einwilligung m&ouml;glich. Sie k&ouml;nnen eine bereits erteilte Einwilligung jederzeit widerrufen. Dazu reicht eine formlose Mitteilung per E-Mail an uns. Die Rechtm&auml;&szlig;igkeit der bis zum Widerruf erfolgten Datenverarbeitung bleibt vom Widerruf unber&uuml;hrt.</p>
<h2 class="mt-8">Beschwerderecht bei der zust&auml;ndigen Aufsichtsbeh&ouml;rde</h2>
<p>Im Falle datenschutzrechtlicher Verst&ouml;&szlig;e steht dem Betroffenen ein Beschwerderecht bei der zust&auml;ndigen Aufsichtsbeh&ouml;rde zu. Zust&auml;ndige Aufsichtsbeh&ouml;rde in datenschutzrechtlichen Fragen ist der Landesdatenschutzbeauftragte des Bundeslandes, in dem unser Unternehmen seinen Sitz hat. Eine Liste der Datenschutzbeauftragten sowie deren Kontaktdaten k&ouml;nnen folgendem Link entnommen werden: <a href="https://www.bfdi.bund.de/DE/Infothek/Anschriften_Links/anschriften_links-node.html" rel="noopener noreferrer" target="_blank">https://www.bfdi.bund.de/DE/Infothek/Anschriften_Links/anschriften_links-node.html.</a></p>
<h2 class="mt-8">SSL- bzw. TLS-Verschl&uuml;sselung</h2>
<p>Diese Seite nutzt aus Sicherheitsgr&uuml;nden und zum Schutz der &Uuml;bertragung vertraulicher Inhalte, wie zum Beispiel Bestellungen oder Anfragen, die Sie an uns als Seitenbetreiber senden, eine SSL-bzw. TLS-Verschl&uuml;sselung. Eine verschl&uuml;sselte Verbindung erkennen Sie daran, dass die Adresszeile des Browsers von &ldquo;http://&rdquo; auf &ldquo;https://&rdquo; wechselt und an dem Schloss-Symbol in Ihrer Browserzeile.</p>
<p>Wenn die SSL- bzw. TLS-Verschl&uuml;sselung aktiviert ist, k&ouml;nnen die Daten, die Sie an uns &uuml;bermitteln, nicht von Dritten mitgelesen werden.</p>
<h2 class="mt-8">Auskunft, Sperrung, L&ouml;schung</h2>
<p>Sie haben im Rahmen der geltenden gesetzlichen Bestimmungen jederzeit das Recht auf unentgeltliche Auskunft &uuml;ber Ihre gespeicherten personenbezogenen Daten, deren Herkunft und Empf&auml;nger und den Zweck der Datenverarbeitung und ggf. ein Recht auf Berichtigung, Sperrung oder L&ouml;schung dieser Daten. Hierzu sowie zu weiteren Fragen zum Thema personenbezogene Daten k&ouml;nnen Sie sich jederzeit unter der im Impressum angegebenen Adresse an uns wenden.</p>
<p><strong>Datenerfassung auf unserer Website</strong></p>
<h2 class="mt-8">Cookies</h2>
<p>Die Internetseiten verwenden teilweise so genannte Cookies. Cookies richten auf Ihrem Rechner keinen Schaden an und enthalten keine Viren. Cookies dienen dazu, unser Angebot nutzerfreundlicher, effektiver und sicherer zu machen. Cookies sind kleine Textdateien, die auf Ihrem Rechner abgelegt werden und die Ihr Browser speichert.</p>
<p>Die meisten der von uns verwendeten Cookies sind so genannte &ldquo;Session-Cookies&rdquo;. Sie werden nach Ende Ihres Besuchs automatisch gel&ouml;scht. Andere Cookies bleiben auf Ihrem Endger&auml;t gespeichert bis Sie diese l&ouml;schen. Diese Cookies erm&ouml;glichen es uns, Ihren Browser beim n&auml;chsten Besuch wiederzuerkennen.</p>
<p>Sie k&ouml;nnen Ihren Browser so einstellen, dass Sie &uuml;ber das Setzen von Cookies informiert werden und Cookies nur im Einzelfall erlauben, die Annahme von Cookies f&uuml;r bestimmte F&auml;lle oder generell ausschlie&szlig;en sowie das automatische L&ouml;schen der Cookies beim Schlie&szlig;en des Browser aktivieren. Bei der Deaktivierung von Cookies kann die Funktionalit&auml;t dieser Website eingeschr&auml;nkt sein.</p>
<p>Cookies, die zur Durchf&uuml;hrung des elektronischen Kommunikationsvorgangs oder zur Bereitstellung bestimmter, von Ihnen erw&uuml;nschter Funktionen (z.B. Warenkorbfunktion) erforderlich sind, werden auf Grundlage von Art. 6 Abs. 1 lit. f DSGVO gespeichert. Der Websitebetreiber hat ein berechtigtes Interesse an der Speicherung von Cookies zur technisch fehlerfreien und optimierten Bereitstellung seiner Dienste. Soweit andere Cookies (z.B.</p>
<p>Cookies zur Analyse Ihres Surfverhaltens) gespeichert werden, werden diese in dieser Datenschutzerkl&auml;rung gesondert behandelt.</p>
<h2 class="mt-8">Server-Log-Dateien</h2>
<p>Der Provider der Seiten erhebt und speichert automatisch Informationen in so genannten Server-Log-Dateien, die Ihr Browser automatisch an uns &uuml;bermittelt. Dies sind: Browsertyp und Browserversion; verwendetes Betriebssystem; Referrer URL; Hostname des zugreifenden Rechners; Uhrzeit der Serveranfrage; IP-Adresse</p>
<p>Eine Zusammenf&uuml;hrung dieser Daten mit anderen Datenquellen wird nicht vorgenommen.</p>
<p>Grundlage f&uuml;r die Datenverarbeitung ist Art. 6 Abs. 1 lit. f DSGVO, der die Verarbeitung von Daten zur Erf&uuml;llung eines Vertrags oder vorvertraglicher Ma&szlig;nahmen gestattet.</p>
<h2 class="mt-8">Analyse Tools und Werbung</h2>
<p>Diese Website nutzt den Dienst &bdquo;Google Analytics&ldquo;, welcher von der Google Inc. (1600 Amphitheatre Parkway Mountain View, CA 94043, USA) angeboten wird, zur Analyse der Websitebenutzung durch Nutzer. Der Dienst verwendet &bdquo;Cookies&ldquo; &ndash; Textdateien, welche auf Ihrem Endger&auml;t gespeichert werden. Die durch die Cookies gesammelten Informationen werden im Regelfall an einen Google-Server in den USA gesandt und dort gespeichert.</p>
<p>Auf dieser Website greift die IP-Anonymisierung. Die IP-Adresse der Nutzer wird innerhalb der Mitgliedsstaaten der EU und des Europ&auml;ischen Wirtschaftsraum gek&uuml;rzt. Durch diese K&uuml;rzung entf&auml;llt der Personenbezug Ihrer IP-Adresse. Im Rahmen der Vereinbarung zur Auftragsdatenvereinbarung, welche die Websitebetreiber mit der Google Inc.</p>
<p>geschlossen haben, erstellt diese mithilfe der gesammelten Informationen eine Auswertung der Websitenutzung und der Websiteaktivit&auml;t und erbringt mit der Internetnutzung verbundene Dienstleistungen.</p>
<p>Sie haben die M&ouml;glichkeit, die Speicherung des Cookies auf Ihrem Ger&auml;t zu verhindern, indem Sie in Ihrem Browser entsprechende Einstellungen vornehmen. Es ist nicht gew&auml;hrleistet, dass Sie auf alle Funktionen dieser Website ohne Einschr&auml;nkungen zugreifen k&ouml;nnen, wenn Ihr Browser keine Cookies zul&auml;sst.</p>
<p>Weiterhin k&ouml;nnen Sie durch ein Browser-Plugin verhindern, dass die durch Cookies gesammelten Informationen (inklusive Ihrer IP-Adresse) an die Google Inc. gesendet und von der Google Inc. genutzt werden. Folgender Link f&uuml;hrt Sie zu dem entsprechenden Plugin: <a href="https://tools.google.com/dlpage/gaoptout?hl=de" rel="noopener noreferrer" target="_blank">https://tools.google.com/dlpage/gaoptout?hl=de</a></p>
<p>Hier finden Sie weitere Informationen zur Datennutzung durch die Google Inc.: <a href="https://support.google.com/analytics/answer/6004245?hl=de" rel="noopener noreferrer" target="_blank">https://support.google.com/analytics/answer/6004245?hl=de</a></p>
<h2 class="mt-8">Konversionsmessung mit dem Conversion-Pixel von Facebook und von https://www.leadpages.net/</h2>
<p>Wir setzen den &ldquo;Conversion-Pixel&ldquo; bzw. Besucheraktions-Pixel der Facebook Inc., 1601 S. California Ave, Palo Alto, CA 94304, USA (&ldquo;Facebook&rdquo;) ein. Au&szlig;erdem den von <a href="https://www.leadpages.net/" rel="noopener noreferrer" target="_blank">https://www.leadpages.net/.</a> Durch den Aufruf dieses Pixels aus Ihrem Browser k&ouml;nnen Facebook <a href="https://www.leadpages.net/" rel="noopener noreferrer" target="_blank">https://www.leadpages.net/</a> in der Folge erkennen, ob eine Werbeanzeige erfolgreich war, also z.B. zu einem online-Kaufabschluss gef&uuml;hrt hat.</p>
<p>Wir erhalten von Facebook und <a href="https://www.leadpages.net/" rel="noopener noreferrer" target="_blank">https://www.leadpages.net/</a> hierzu ausschlie&szlig;lich statistische Daten ohne Bezug zu einer konkreten Person. So k&ouml;nnen wir die Wirksamkeit der Werbeanzeigen f&uuml;r statistische und Marktforschungszwecke erfassen. Insbesondere falls Sie bei Facebook angemeldet sind, verweisen wir im &Uuml;brigen auf deren Datenschutzinformationen <a href="https://www.facebook.com/ads/preferences/" rel="noopener noreferrer" target="_blank">https://www.facebook.com/ads/preferences/.</a></p>
<p>Bitte gehen Sie auf <a href="https://www.facebook.com/ads/preferences/" rel="noopener noreferrer" target="_blank">https://www.facebook.com/ads/preferences/,</a> wenn Sie Ihre Einwilligung zu Conversion Pixel widerrufen m&ouml;chten.</p>
<h2 class="mt-8">Taboola Pixel &amp; Tracking:</h2>
<p>Taboola ist ein separater und unabh&auml;ngiger Controller der Daten unserer Kunden, welche Taboola auf den Landing Pages unserer Kunden sammelt.</p>
<p>Beide Parteien sammeln unabh&auml;ngig voneinander Informationen &uuml;ber die Besucher der Webseite des Werbetreibenden und treffen unabh&auml;ngig voneinander Entscheidungen dar&uuml;ber, wie diese Daten verarbeitet werden. Taboola st&uuml;tzt sich bei der Verarbeitung der erhobenen personenbezogenen Daten nicht auf Anweisungen unserer Werbepartner &ndash; wir treffen unsere eigenen Entscheidungen hinsichtlich der Verarbeitung der Daten.</p>
<p>Der Taboola Universal Pixel sendet Taboola Signale von der Interaktion der Nutzer mit den Inhalten des Werbetreibenden. Unser Algorithmus verwendet diese Daten dann f&uuml;r die Analyse und zur Optimierung der Empfehlungen, die wir auf der Grundlage der Klicks, Seitenaufrufe und Conversions der einzelnen Nutzer abgeben.</p>
<p>Unsere Tags und Pixel sammeln auf der Webseite des Werbetreibenden Informationen &uuml;ber Seitenaufrufe und Aktionen (Clicks und Conversions) die mit einer gehashten Taboola-Benutzer-ID auf Kundenseite verkn&uuml;pft sind.</p>
<p>Konkret:<br/>1. Ereignisse von der Website des Werbetreibenden<br/>2. Informationen &uuml;ber den Browser des Users gelesenen vom User Agent<br/>Erste und nachfolgende Seitenbesuche auf der Website des Werbetreibenden<br/>Conversion Data<br/>Die zugeh&ouml;rige Hash-Taboola-Benutzer-ID aus dem Cookie<br/>Engagement-Signale &ndash; time on site, scroll Tiefe, Session Tiefe<br/>Inklusive Betriebssystem, Browsertyp und Browserversion</p>
<p>Die Daten werden auf unseren Servern und zus&auml;tzlich als Backup in einer Cloud gespeichert. Unsere Server stehen in der EU, Israel, US, Singapore und Hong Kong. Speziell verarbeitete Daten werden in Israel gespeichert und Rohdaten speichern wir in den USA sowie in der Google Cloud.</p>
<p>Diese Webseite nutzt Taboola&rsquo;s Content Discovery Technologie um Ihnen weitere Online Inhalte zu empfehlen, die Sie interessieren k&ouml;nnten. Um diese Empfehlungen zu steuern sammelt Taboola Informationen zu Ihrem Ger&auml;t und Ihrem Verhalten auf dieser Webseite (und anderen Partner Seiten) durch Cookies und &auml;hnliche Technologien. Um mehr Informationen zu erhalten, finden Sie hier Taboola&rsquo;s Datenschutz Richtlinien oder klicken Sie hier f&uuml;r das Opt-Out.</p>
<p>Im Allgemeinen ist es nicht unbedingt notwendig eine Passage von uns in Eurer Privacy Policy zu integrieren, da wir ein unabh&auml;ngiger Controller und nicht Teil Eures Datenverarbeitungsprozesses sind. Zumal wir ein unabh&auml;ngiger Controller und kein Prozessor der Daten sind, haben wir auch keinen AVV.</p>
<p>Relevante Infos findet Ihr auf unserer Webseite: <a href="https://www.taboola.com/privacy-policy#optout" rel="noopener noreferrer" target="_blank">https://www.taboola.com/privacy-policy#optout.</a> Weiterf&uuml;hrende Infos zu unserem Cookie k&ouml;nnt Ihr hier einsehen: <a href="https://www.taboola.com/cookie-policy" rel="noopener noreferrer" target="_blank">https://www.taboola.com/cookie-policy</a></p>
<p><strong>Plugins und Tools</strong></p>
<h2 class="mt-8">Google Web Fonts</h2>
<p>Diese Seite nutzt zur einheitlichen Darstellung von Schriftarten so genannte Web Fonts, die von Google bereitgestellt werden. Beim Aufruf einer Seite l&auml;dt Ihr Browser die ben&ouml;tigten Web Fonts in ihren Browsercache, um Texte und Schriftarten korrekt anzuzeigen.</p>
<p>Zu diesem Zweck muss der von Ihnen verwendete Browser Verbindung zu den Servern von Google aufnehmen. Hierdurch erlangt Google Kenntnis dar&uuml;ber, dass &uuml;ber Ihre IP-Adresse unsere Website aufgerufen wurde. Die Nutzung von Google Web Fonts erfolgt im Interesse einer einheitlichen und ansprechenden Darstellung unserer Online-Angebote. Dies stellt ein berechtigtes Interesse im Sinne von Art. 6 Abs. 1 lit. f DSGVO dar.</p>
<p>Wenn Ihr Browser Web Fonts nicht unterst&uuml;tzt, wird eine Standardschrift von Ihrem Computer genutzt.</p>
<p>Weitere Informationen zu Google Web Fonts finden Sie unter <a href="https://developers.google.com/fonts/faq" rel="noopener noreferrer" target="_blank">https://developers.google.com/fonts/faq</a> und in der Datenschutzerkl&auml;rung von Google: <a href="https://www.google.com/policies/privacy/" rel="noopener noreferrer" target="_blank">https://www.google.com/policies/privacy/.</a></p>
<h2 class="mt-8">Google Maps</h2>
<p>Diese Seite nutzt &uuml;ber eine API den Kartendienst Google Maps. Anbieter ist die Google Inc., 1600 Amphitheatre Parkway, Mountain View, CA 94043, USA.</p>
<p>Zur Nutzung der Funktionen von Google Maps ist es notwendig, Ihre IP Adresse zu speichern. Diese Informationen werden in der Regel an einen Server von Google in den USA &uuml;bertragen und dort gespeichert. Der Anbieter dieser Seite hat keinen Einfluss auf diese Daten&uuml;bertragung.</p>
<p>Die Nutzung von Google Maps erfolgt im Interesse einer ansprechenden Darstellung unserer Online-Angebote und an einer leichten Auffindbarkeit der von uns auf der Website angegebenen Orte. Dies stellt ein berechtigtes Interesse im Sinne von Art. 6 Abs. 1 lit. f DSGVO dar.</p>
<p>Mehr Informationen zum Umgang mit Nutzerdaten finden Sie in der Datenschutzerkl&auml;rung von Google: <a href="https://www.google.de/intl/de/policies/privacy/" rel="noopener noreferrer" target="_blank">https://www.google.de/intl/de/policies/privacy/.</a></p>`);
}

function erstinformationMain() {
  return legalTextMain(`<h1>Erstinformation</h1>
<h2 class="mt-8">1. Firma und Anschrift:</h2>
<p>Ludwig Oelze<br/>Ludwig Oelze<br/>Bismarckstr. 26<br/>76530 Baden-Baden</p>
<h2 class="mt-8">2. Status des Vermittlers nach Gewerbeordnung:</h2>
<p>Wir sind als Versicherungsmakler nach &sect; 34d Abs. 1 der Gewerbeordnung t&auml;tig und im Vermittlerregister unter der Nummer D-KQ7W-92T3Y-75 registriert.</p>
<h2 class="mt-8">3. Bei Interesse k&ouml;nnen Sie die Angaben bei der Registerstelle &uuml;berpr&uuml;fen:</h2>
<p>Deutsche Industrie- und Handelskammer (DIHK)<br/>Breite Stra&szlig;e 29<br/>10178 Berlin<br/>Tel.: 0180-600-585-0*<br/>* 20 Cent/Anruf aus dem deutschen Festnetz<br/><a href="http://www.vermittlerregister.info" rel="noopener noreferrer" target="_blank">http://www.vermittlerregister.info</a></p>
<h2 class="mt-8">4. Schlichtungsstellen f&uuml;r au&szlig;ergerichtliche Streitbeilegung:</h2>
<p>Versicherungsombudsmann e.V.<br/>Postfach 08 06 32<br/>10006 Berlin</p>
<p>Ombudsmann private Kranken- und Pflegeversicherung<br/>Postfach 06 02 22<br/>10052 Berlin</p>
<h2 class="mt-8">5.</h2>
<p>Der Vermittler h&auml;lt keine unmittelbare oder mittelbare Beteiligung von mehr als 10 % der Stimmrechte oder des Kapitals an einem Versicherungsunternehmen.</p>
<h2 class="mt-8">6.</h2>
<p>Ein Versicherungsunternehmen h&auml;lt keine mittelbare oder unmittelbare Beteiligung von mehr als 10 % der Stimmrechte oder des Kapitals am Versicherungsmakler.</p>
<h2 class="mt-8">7.</h2>
<p>Der Vermittler erh&auml;lt f&uuml;r die Vermittlung von Versicherungsvertr&auml;gen eine Courtage des Produktgebers. Eine gesonderte Verg&uuml;tung durch den Kunden wird nicht geschuldet.</p>
<p>Sofern die Verg&uuml;tung aufgrund der W&uuml;nsche und Bed&uuml;rfnisse durch den Kunden erfolgt, wird hierf&uuml;r eine separate Verg&uuml;tungsvereinbarung getroffen.</p>
<h2 class="mt-8">8.</h2>
<p>Die T&auml;tigkeit beinhaltet auch Beratung.</p>
<h2 class="mt-8">9.</h2>
<p>Es werden in regelm&auml;&szlig;igen Intervallen die Produktanbieter sondiert. Hierbei wird mit Produktanbietern, welche dem Makler Informationen zur Produktinformationen zur Verf&uuml;gung stellen, eine Vermittlungsvereinbarung geschlossen. Es werden nur Versicherer ber&uuml;cksichtigt, die in Deutschland zum Vertrieb zugelassen sind bzw. der Aufsicht der Bundesanstalt f&uuml;r Finanzdienstleistungsaufsicht unterliegen, ihren Sitz oder eine Niederlassung in der Bundesrepublik Deutschland haben und die Vertragsbedingungen in deutscher Sprache anbieten.</p>
<p>Auf Wunsch wird Ihnen eine aktuelle Liste der kooperierenden Versicherer zur Verf&uuml;gung gestellt.</p>
<h2 class="mt-8">Ludwig Oelze</h2>
<p>Bismarckstr. 26<br/>76530 Baden-Baden</p>
<p>Ludwig Oelze<br/>Tel.: +4917643689181<br/>Registrierungsnr.: D-KQ7W-92T3Y-75<br/>E-Mail: <a href="mailto:finanzen@ludwigoelze.com">finanzen@ludwigoelze.com</a><br/>Homepage: <a href="https://ludwigoelze.com" rel="noopener noreferrer" target="_blank">https://ludwigoelze.com</a></p>`);
}

function passportCardDeMain() {
  return `<main>
<section class="hero hero-small">
<div class="hero-bg"><img alt="Ludwig Oelze im Gespr&auml;ch" loading="eager" src="Ludwig_prev_foto/_X8A2955_prev.webp"/></div>
<div class="hero-overlay" style="background: linear-gradient(135deg, rgba(8, 37, 37, 0.84) 0%, rgba(18, 64, 64, 0.78) 45%, rgba(201, 169, 98, 0.52) 100%);"></div>
<div class="hero-content">
<span class="hero-badge">${icon.globe} Auslandskrankenversicherung</span>
<h1><span class="highlight">PassportCard</span>: Auslandskrankenversicherung ohne klassische Vorkasse</h1>
<p class="hero-subtitle">Deine Gesundheit im Ausland, so einfach wie eine Kartenzahlung: keine klassische Vorkasse, keine Formulare, keine Wartezeit auf Erstattung und schnelle deutschsprachige Unterst&uuml;tzung, wenn Du medizinische Hilfe brauchst.</p>
</div>
</section>
<section class="section bg-white">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>Medizinische Rechnungen direkt mit Karte abwickeln</h2>
<p>PassportCard ist f&uuml;r Menschen gedacht, die im Ausland nicht erst hohe Arztrechnungen vorfinanzieren und danach lange auf Erstattung warten wollen. Im Behandlungsfall wird die pers&ouml;nliche Mastercard-Debitkarte f&uuml;r die voraussichtlichen medizinischen Kosten aktiviert und kann direkt beim Arzt oder in der Klinik genutzt werden.</p>
<p>Der praktische Kern ist genau dieser Unterschied: Arztkosten direkt bezahlen, weltweit freie Arztwahl nutzen und im Hintergrund einen Service haben, der rund um die Uhr erreichbar ist.</p>
<p>Das macht die L&ouml;sung besonders interessant f&uuml;r Expats, digitale Nomaden, Rentner im Ausland, Familien mit internationalem Alltag und l&auml;ngere Auslandsaufenthalte.</p>
<p>Ich pr&uuml;fe dabei nicht nur das Produkt, sondern auch Dein Aufenthaltsland, Deine R&uuml;ckkehrpl&auml;ne, den gew&uuml;nschten Leistungsumfang und bestehende Versicherungen.</p>
</div>
<div class="card reveal reveal-right">
<h3>Der praktische Kern</h3>
${list(['Keine Vorkasse, keine Formulare, keine Wartezeit auf Erstattung', 'Pers&ouml;nliche Mastercard-Debitkarte f&uuml;r medizinische Kosten', '24/7 deutschsprachiger Service, laut Anbieterangaben innerhalb von etwa 10 Sekunden erreichbar', 'Weltweite freie Arztwahl ohne enges Pflichtnetzwerk', 'Digitale Steuerung per App und Kartenaufladung in Echtzeit'])}
</div>
</div>
</div>
</section>
<section class="section bg-off-white">
<div class="container">
<div class="section-header reveal"><h2>Deine Vorteile mit PassportCard</h2></div>
<div class="grid grid-4">
${card('Keine klassische Vorkasse', 'Arztrechnungen m&uuml;ssen nicht erst voll privat ausgelegt und anschlie&szlig;end m&uuml;hsam eingereicht werden.', icon.card, 'stagger-1')}
${card('24/7 deutschsprachiger Service', 'Pers&ouml;nliche Hilfe ist laut Anbieterangaben rund um die Uhr und sehr schnell erreichbar, auch wenn Du im Ausland unter Zeitdruck stehst.', icon.clock, 'stagger-2')}
${card('Freie Arztwahl weltweit', 'Du bist nicht auf ein enges Netzwerk beschr&auml;nkt; entscheidend ist, dass die konkrete Leistung und Nutzung zum Tarif passt.', icon.globe, 'stagger-3')}
${card('0 EUR Selbstbeteiligung', 'Bei berechtigten Leistungen kann die Kosten&uuml;bernahme ohne Selbstbeteiligung erfolgen; Details m&uuml;ssen sauber im Tarif gepr&uuml;ft werden.', icon.check, 'stagger-4')}
${card('Digitale Abwicklung per App', 'Anruf oder App-Tap gen&uuml;gt, damit der Fall gepr&uuml;ft und die Karte mit dem ben&ouml;tigten Betrag geladen werden kann.', icon.file, 'stagger-5')}
${card('R&uuml;ckdeckung durch Allianz', 'PassportCard stellt die Allianz-R&uuml;ckdeckung als starken Versicherungspartner heraus.', icon.shield, 'stagger-6')}
</div>
</div>
</section>
<section class="section bg-white">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>So funktioniert es im Alltag</h2>
<p>Wenn Du medizinische Hilfe brauchst, reichen ein kurzer Anruf oder ein App-Tap. Nach Pr&uuml;fung wird die PassportCard mit dem ben&ouml;tigten Betrag aufgeladen, sodass Du die Rechnung direkt bezahlen kannst. Genau diese unmittelbare Prozesslogik unterscheidet PassportCard von vielen klassischen Auslandskrankenversicherungen.</p>
<p>Wenn Du an einem Ort bist, an dem Mastercard nicht akzeptiert wird, bleibt der klassische Weg m&ouml;glich: Rechnung einreichen und Kosten erstatten lassen.</p>
<p>Wichtig bleibt: Der einfache Ablauf ersetzt keine fachliche Auswahl. Leistungsniveau, Aufenthaltsland, Klinikrealit&auml;t und langfristige Absicherung m&uuml;ssen zueinander passen.</p>
</div>
<div class="card reveal reveal-right">
<h3>Typische Einsatzfelder</h3>
${list(['Arztbesuche im Ausland mit direkter Kartenzahlung', 'Behandlungen in internationalen Kliniken', 'L&auml;ngere Aufenthalte au&szlig;erhalb Deutschlands', 'Dubai, Spanien und andere internationale Setups', 'USA-F&auml;lle, bei denen spezielle Partnernetzwerke relevant sein k&ouml;nnen'])}
</div>
</div>
</div>
</section>
<section class="section bg-off-white">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>F&uuml;r wen ich PassportCard pr&uuml;fe</h2>
<p>PassportCard kann spannend sein, wenn Du im Ausland lebst, regelm&auml;&szlig;ig zwischen L&auml;ndern pendelst oder medizinische Versorgung im Ausland m&ouml;glichst unkompliziert nutzen m&ouml;chtest.</p>
<p>Gerade bei Expats, digitalen Nomaden, Rentnern im Ausland und Familien mit internationalem Alltag kommt es darauf an, dass die Versicherung nicht nur auf dem Papier gut aussieht, sondern im Ernstfall praktisch funktioniert.</p>
</div>
<div class="card reveal reveal-right">
<h3>Ideal f&uuml;r</h3>
${list(['Expats und Auswanderer', 'Digitale Nomaden und Remote Worker', 'Rentner mit dauerhaftem Auslandsaufenthalt', 'Familien mit internationalem Lebensmittelpunkt'], true)}
</div>
</div>
</div>
</section>
<section class="section bg-white">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>Empfohlen von vielen Auslandsversicherten</h2>
<p>PassportCard hebt hervor, dass 9 von 10 Kunden die L&ouml;sung weiterempfehlen. Entscheidend ist f&uuml;r mich trotzdem nicht nur eine gute Bewertung, sondern ob die L&ouml;sung zu Deinem konkreten Aufenthaltsprofil passt.</p>
<p>Gerade im Ausland z&auml;hlen im Leistungsfall drei Dinge: schnelle Erreichbarkeit, klare Kostenabwicklung und die M&ouml;glichkeit, medizinische Hilfe ohne unn&ouml;tige finanzielle Vorleistung zu bekommen.</p>
</div>
<div class="card reveal reveal-right">
<h3>Worauf ich achte</h3>
${list(['Passt die freie Arztwahl zu Deinem Zielland?', 'Sind Selbstbeteiligung und Leistungsh&ouml;he realistisch?', 'Wie funktioniert die App- und Kartenlogik im Aufenthaltsland?', 'Gibt es bestehende Vertr&auml;ge, die erhalten oder angepasst werden sollten?'])}
</div>
</div>
</div>
</section>
<section class="section bg-white">
<div class="container container-narrow">
<div class="section-header reveal"><h2>H&auml;ufige Fragen</h2></div>
${accordion([
  { q: 'Wie funktioniert die PassportCard?', a: 'Die PassportCard ist eine Mastercard-Debitkarte, die bei Bedarf mit dem ben&ouml;tigten Betrag f&uuml;r Deine medizinische Behandlung aufgeladen wird. Ein Anruf oder App-Tap gen&uuml;gt, danach kannst Du direkt beim Arzt oder in der Klinik bezahlen.' },
  { q: 'Muss ich in Vorleistung gehen?', a: 'Nein, genau das ist der besondere Nutzen der PassportCard. Du musst berechtigte Rechnungen in vielen F&auml;llen nicht erst vorstrecken und auf Erstattung warten; die Karte wird direkt mit dem ben&ouml;tigten Betrag geladen.' },
  { q: 'Wo kann ich PassportCard nutzen?', a: 'Die Karte kann weltweit &uuml;berall dort eingesetzt werden, wo Mastercard akzeptiert wird. In den USA k&ouml;nnen f&uuml;r die Abrechnung spezielle Partnernetzwerke relevant sein.' },
  { q: 'Gibt es eine Altersbegrenzung?', a: 'Anbieterangaben nennen einen Zugang von 18 bis &uuml;ber 80 Jahre. Auch im h&ouml;heren Alter kann ein Abschluss m&ouml;glich sein; Alter, Gesundheitsangaben und Leistungswunsch pr&uuml;fe ich vor einer Empfehlung sauber zusammen.' },
  { q: 'Wie schnell erhalte ich Hilfe im Notfall?', a: 'Der deutschsprachige 24/7-Service wird in den Anbieterangaben mit einer Erreichbarkeit von etwa 10 Sekunden beschrieben. Die Kartenaufladung kann in Echtzeit erfolgen, damit Behandlung nicht an Papierkram scheitert.' },
  { q: 'Was passiert, wenn Mastercard nicht akzeptiert wird?', a: 'Dann kann der klassische Erstattungsweg genutzt werden: Rechnung einreichen und die Kosten nach den Tarifbedingungen erstatten lassen.' },
  { q: 'Ist PassportCard automatisch die beste L&ouml;sung?', a: 'Nein. Das Produkt kann sehr gut passen, aber es muss zur Aufenthaltslogik, zu bestehenden Vertr&auml;gen und zu Deinen medizinischen Erwartungen passen.' },
])}
</div>
</section>
<section class="cta-section" id="contact">
<div class="container">
<h2 class="reveal">PassportCard passend einordnen</h2>
<p class="reveal">Wenn Du wissen willst, ob PassportCard zu Deinem Auslandssetup passt, schauen wir gemeinsam auf Aufenthaltsland, Leistungswunsch und praktische Nutzung im Ernstfall.</p>
<div class="cta-actions">
<a class="btn btn-primary btn-lg" href="https://angebot.passportcard.de/Purchase?AffiliateId=rvLjypTGwRN5%2BeE1plbd%2Fg%3D%3D&amp;AffiliateAgentId=jk%2BFj7xbxEN4xYuu4xyLsg%3D%3D" rel="noopener noreferrer" target="_blank">PassportCard anfragen</a>
<a class="btn btn-secondary btn-lg" href="https://wa.me/4917643689181" rel="noopener noreferrer" target="_blank">Pers&ouml;nliche R&uuml;ckfrage</a>
</div>
</div>
</section>
</main>`;
}

function passportCardEnMain() {
  return `<main>
<section class="hero hero-small">
<div class="hero-bg"><img alt="Ludwig Oelze in consultation" loading="eager" src="Ludwig_prev_foto/_X8A2955_prev.webp"/></div>
<div class="hero-overlay" style="background: linear-gradient(135deg, rgba(8, 37, 37, 0.84) 0%, rgba(18, 64, 64, 0.78) 45%, rgba(201, 169, 98, 0.52) 100%);"></div>
<div class="hero-content">
<span class="hero-badge">${icon.globe} Dubai expat health cover</span>
<h1>Live freely in Dubai with <span class="highlight">PassportCard</span></h1>
<p class="hero-subtitle">Reliable international health insurance for Dubai expats: no upfront payments, no forms, no waiting for reimbursements and 24/7 English-speaking support when you need medical care abroad.</p>
</div>
</section>
<section class="section bg-white">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>Your health in Dubai, as simple as a card payment</h2>
<p>PassportCard is built for people who do not want to advance large medical bills and then wait for reimbursement. In a medical case, your personal Mastercard debit card can be activated for the expected treatment cost and used directly at the clinic or doctor.</p>
<p>Dubai's healthcare costs can be high, especially in premium hospitals and international clinics. PassportCard is therefore especially relevant if you live in Dubai, move between countries or want access to care without having to advance thousands of dirhams first.</p>
<p>PassportCard information references Dubai facilities such as Mediclinic, Saudi German Hospital, American Hospital and NMC Healthcare. Before you decide, I still check whether the practical clinic access fits your residence status, family setup and benefit expectations.</p>
</div>
<div class="card reveal reveal-right">
<h3>What makes it practical</h3>
${list(['No upfront payments, no forms and no waiting for reimbursement in many cases', 'Personal Mastercard debit card for medical expenses', '24/7 English-speaking service, described by the provider as available within about 10 seconds', 'Designed for Dubai residents and real expat life, not only short trips', 'Usable wherever Mastercard is accepted, with reimbursement still possible where it is not'])}
</div>
</div>
</div>
</section>
<section class="section bg-off-white">
<div class="container">
<div class="section-header reveal"><h2>Your benefits with PassportCard in Dubai</h2></div>
<div class="grid grid-4">
${card('No upfront payments', 'Avoid advancing high treatment bills whenever the card process applies.', icon.card, 'stagger-1')}
${card('24/7 English support', 'Get help when medical decisions cannot wait for office hours, with Dubai time zone support.', icon.clock, 'stagger-2')}
${card('Free choice across Dubai and UAE', 'PassportCard is positioned without a narrow mandatory provider network.', icon.globe, 'stagger-3')}
${card('0 EUR deductible', 'Eligible services can be covered without deductible; the exact scope must still match the selected plan.', icon.check, 'stagger-4')}
${card('App-based processing', 'A quick call or app tap starts the process and the card can be loaded with the required amount.', icon.file, 'stagger-5')}
${card('Backed by Allianz', 'The PassportCard product copy highlights Allianz backing as a strong insurance partner.', icon.shield, 'stagger-6')}
${card('Wellness-related extras', 'Provider information mentions prescribed massages, up to 12 sessions per year, and vision care benefits for glasses or contact lenses.', icon.check, 'stagger-1')}
${card('Major Dubai facilities', 'The card logic is presented for providers such as Mediclinic, Saudi German Hospital, American Hospital and NMC Healthcare where Mastercard acceptance applies.', icon.card, 'stagger-2')}
</div>
</div>
</section>
<section class="section bg-white">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>How it works in everyday Dubai life</h2>
<p>If you need treatment, a quick call or app tap is all it takes. After the case is checked, the card can be loaded with the required amount in AED so you can pay the medical bill directly at the clinic or hospital.</p>
<p>If you happen to be in a location where Mastercard is not accepted, you can still submit the bill and receive reimbursement according to the selected plan.</p>
<p>Before choosing it, we still check whether the coverage fits your residence status, family setup, expected clinic level and long-term plans.</p>
</div>
<div class="card reveal reveal-right">
<h3>Typical use cases</h3>
${list(['Doctor visits and outpatient treatment in Dubai', 'International clinics and hospitals with high upfront payment expectations', 'Dubai residence with worldwide movement', 'Families and professionals with high service expectations', 'Emergency treatment where speed and English support matter'])}
</div>
</div>
</div>
</section>
<section class="section bg-off-white">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>Built around Dubai's premium healthcare reality</h2>
<p>Dubai's private healthcare system can be excellent, but it is also expensive. The Dubai-focused product copy strongly addresses the practical stress of upfront payments at premium clinics and hospitals. PassportCard addresses exactly that moment: you should be able to focus on treatment instead of paperwork and liquidity.</p>
<p>The 24/7 service is described as aligned with Dubai time zone (GMT+4), so support is available when you need it in the UAE, not only during European office hours.</p>
</div>
<div class="card reveal reveal-right">
<h3>Practical Dubai checks</h3>
${list(['Which clinics and hospitals do you realistically use?', 'Does your family need outpatient, inpatient, dental, vision or wellness-related benefits?', 'Do you need Dubai only, UAE plus Europe, or worldwide movement?', 'Is a local, international or combined insurance setup more robust?'])}
</div>
</div>
</div>
</section>
<section class="section bg-off-white">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>Who should take a closer look</h2>
<p>PassportCard can be a strong fit if you live in Dubai, travel regularly, run an international business or want medical access abroad to be practical, fast and predictable.</p>
<p>The right answer still depends on your country mix, medical history, family situation and whether you need a local, international or combined insurance structure.</p>
</div>
<div class="card reveal reveal-right">
<h3>Especially relevant for</h3>
${list(['Dubai expats and entrepreneurs', 'Remote workers and digital nomads', 'International families', 'People splitting time between UAE, Europe and other countries'], true)}
</div>
</div>
</div>
</section>
<section class="section bg-white">
<div class="container container-narrow">
<div class="section-header reveal"><h2>Frequently Asked Questions</h2></div>
${accordion([
  { q: 'How does PassportCard work in Dubai?', a: 'PassportCard is a Mastercard debit card that can be loaded with the required amount for your medical treatment when needed. A simple call or app tap starts the process, and you can pay directly at a Dubai clinic or hospital that accepts Mastercard.' },
  { q: 'Do I have to pay upfront at Dubai hospitals?', a: 'No, avoiding the classic upfront payment and reimbursement process is the core benefit. The card can be loaded directly with the required amount, which is especially valuable in Dubai where medical costs can be high.' },
  { q: 'Which Dubai hospitals accept PassportCard?', a: 'The product information references major Dubai and UAE providers such as Mediclinic, Saudi German Hospital, American Hospital and NMC Healthcare. In practice, PassportCard works where Mastercard is accepted, so clinic access should still be checked against your expectations.' },
  { q: 'Is there an age limit for Dubai residents?', a: 'Provider information states that PassportCard is available from age 18 to over 80. Coverage can therefore be possible at an advanced age, but eligibility still depends on plan, health information and underwriting.' },
  { q: 'How quickly can I get help in an emergency in Dubai?', a: 'Provider information describes the 24/7 English-speaking service as available within about 10 seconds. Card loading can happen in real time, so treatment is not delayed by reimbursement paperwork.' },
  { q: 'What additional benefits are included for Dubai lifestyle?', a: 'Provider information highlights additional benefits such as up to 12 prescribed massage sessions per year and vision aids like glasses or contact lenses. These benefits must still be checked against the exact selected plan.' },
  { q: 'Can I use it outside Dubai?', a: 'PassportCard is an international solution, but country scope and benefits depend on the selected plan. This should be matched to your actual travel pattern.' },
  { q: 'Do I still need advice?', a: 'Yes. PassportCard may be a strong product, but the right setup depends on residence, visa logic, family needs and existing coverage.' },
])}
</div>
</section>
<section class="cta-section" id="contact">
<div class="container">
<h2 class="reveal">Want a quick English check before you decide?</h2>
<p class="reveal">Send me your country setup and I will help you understand whether PassportCard is a good fit or whether another international structure makes more sense.</p>
<div class="cta-actions">
<a class="btn btn-primary btn-lg" href="https://angebot.passportcard.de/Purchase?AffiliateId=rvLjypTGwRN5%2BeE1plbd%2Fg%3D%3D&amp;AffiliateAgentId=jk%2BFj7xbxEN4xYuu4xyLsg%3D%3D" rel="noopener noreferrer" target="_blank">Request PassportCard</a>
<a class="btn btn-secondary btn-lg" href="https://wa.me/4917643689181" rel="noopener noreferrer" target="_blank">Ask Ludwig</a>
</div>
</div>
</section>
</main>`;
}

function durchblickMain() {
  return `<main>
<section class="hero hero-small">
<div class="hero-bg"><img alt="Ludwig Oelze Beratung" loading="eager" src="Ludwig_prev_foto/_X8A3120_prev.webp"/></div>
<div class="hero-overlay" style="background: linear-gradient(135deg, rgba(8, 37, 37, 0.86) 0%, rgba(18, 64, 64, 0.78) 45%, rgba(201, 169, 98, 0.50) 100%);"></div>
<div class="hero-content">
<span class="hero-badge">${icon.card} ETF-Vorsorge</span>
<h1>Hey! Kennst Du schon <span class="highlight">Durchblick?</span></h1>
<p class="hero-subtitle">Deine ETF-Vorsorge der neuen Generation</p>
</div>
</section>
<section class="section bg-white durchblick-intro-section">
<div class="container">
<div class="two-col durchblick-intro-grid">
<div class="reveal">
<h2>Mach mehr aus Deinem Geld</h2>
<p>Mit Durchblick, der cleveren ETF-Vorsorge, sparst Du flexibel und sicher &ndash; ohne Abschlussgeb&uuml;hren, dank meiner exklusiven Kooperation:</p>
${list(['Renditechancen und Sicherheit: W&auml;hle Deine individuelle Anlagestrategie.', 'Steuervorteile nutzen: Deine Ertr&auml;ge w&auml;hrend der Ansparphase sind steuerfrei.', 'Flexibel bleiben: Du kannst jederzeit ein- und auszahlen.', 'Lebenslange Rente: Genie&szlig;e Deinen Ruhestand sorgenfrei.'])}
<p>Starte schon ab 10 &euro; im Monat! Schreib mir einfach zur&uuml;ck oder ruf mich an. Ich erkl&auml;re Dir in wenigen Minuten, wie es funktioniert.</p>
<div class="cta-actions cta-actions-left">
<a class="btn btn-primary btn-lg" href="https://calendly.com/einsparung/beratung" rel="noopener noreferrer" target="_blank">Termin buchen</a>
</div>
</div>
<div class="card durchblick-phone-card reveal reveal-right">
<div class="durchblick-phone-preview">
<img alt="Smartphone-App-Ansicht der Durchblick ETF-Vorsorge" src="assets/images/durchblick/durchblick-app.webp" loading="lazy"/>
</div>
</div>
</div>
</div>
</section>
<section class="section bg-off-white" id="dokumente">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>Dokumente</h2>
</div>
<div class="card reveal reveal-right">
<h3>Downloads</h3>
<div class="cta-actions cta-actions-stack">
<a class="btn btn-secondary btn-lg" href="${durchblickLinks.kostenvergleich}" rel="noopener noreferrer" target="_blank">Kostenvergleich herunterladen</a>
<a class="btn btn-secondary btn-lg" href="${durchblickLinks.produktinformationen}" rel="noopener noreferrer" target="_blank">Produktinformationen herunterladen</a>
</div>
</div>
</div>
</div>
</section>
</main>`;
}

function ankuendigungMain() {
  return `<main>
<section class="hero hero-small">
<div class="hero-bg"><img alt="Ludwig Oelze Beratung" loading="eager" src="Ludwig_prev_foto/_X8A3007_prev.webp"/></div>
<div class="hero-overlay" style="background: linear-gradient(135deg, rgba(8, 37, 37, 0.88) 0%, rgba(18, 64, 64, 0.78) 46%, rgba(201, 169, 98, 0.48) 100%);"></div>
<div class="hero-content">
<span class="hero-badge">${icon.clock} Mandanteninformation</span>
<h1>Ank&uuml;ndigung</h1>
<p class="hero-subtitle">Pilotprojekt f&uuml;r besseren Kundenservice</p>
</div>
</section>
<section class="section bg-white ankuendigung-intro-section">
<div class="container">
<div class="two-col ankuendigung-intro-grid">
<div class="reveal">
<span class="section-badge" data-current-month-stand data-current-month-prefix="Stand">Stand ${currentStand.label}</span>
<h2>Liebe Mandantinnen und Mandanten</h2>
<p>ich starte ein Pilotprojekt, um meinen Kundenservice noch effizienter und strukturierter zu gestalten. Mein Ziel ist es, dir nicht nur die beste Beratung, sondern auch klare und einfache Prozesse f&uuml;r deine Anliegen zu bieten.</p>
</div>
<div class="two-col-image reveal reveal-right">
<img alt="Ludwig Oelze l&auml;chelnd im Gespr&auml;chsumfeld" src="assets/images/ankuendigung/ludwig-service-update.webp" loading="lazy"/>
</div>
</div>
</div>
</section>
<section class="section bg-off-white" id="ablauf">
<div class="container">
<div class="section-header reveal">
<h2>Was sich konkret &auml;ndert</h2>
<a class="btn btn-primary btn-lg mt-4" href="${calendlyLinks.weitereFragen}" rel="noopener noreferrer" target="_blank">Termin f&uuml;r weitere Fragen buchen</a>
</div>
<div class="grid grid-2">
<div class="card reveal stagger-1">
<div class="card-icon">${icon.clock}</div>
<h3>1. Anliegen, Angebote und Fragen nur noch per Termin</h3>
<p>Ab sofort k&ouml;nnen Fragen zu Vertr&auml;gen, Angeboten und weiteren Anliegen, die du bisher per E-Mail, WhatsApp oder telefonisch gestellt hast, nur noch &uuml;ber einen Termin bearbeitet werden.</p>
<p>Das stellt sicher, dass du immer genau wei&szlig;t, wann dein Anliegen bearbeitet wird, und ich f&uuml;r R&uuml;ckfragen direkt erreichbar bin &ndash; sei es telefonisch oder per WhatsApp. Bitte stelle sicher, dass auch du w&auml;hrend des gebuchten Zeitfensters erreichbar bist.</p>
<p>Viele Fragen lassen sich &uuml;brigens auch schnell &uuml;ber meine App kl&auml;ren. Weitere Informationen dazu findest du unten.</p>
<p>F&uuml;r dringende Anliegen, bei denen kein zeitnaher Termin verf&uuml;gbar ist, sende bitte eine E-Mail oder WhatsApp Nachricht mit dem Betreff „DRINGEND“, damit ich diese priorisiert bearbeiten und ggf. zur&uuml;ckrufen kann.</p>
<p>F&uuml;r vollst&auml;ndige Beratungstermine zu neuen Produkten nutze bitte diesen Link:</p>
<a class="knowledge-card-link" href="${calendlyLinks.weitereFragen}" rel="noopener noreferrer" target="_blank">${calendlyLinks.weitereFragen}</a>
</div>
<div class="card reveal stagger-2">
<div class="card-icon">${icon.shield}</div>
<h3>2. Schadenf&auml;lle</h3>
<p>Schadenmeldungen und die Einreichung von Rechnungen erfolgen ab sofort &uuml;ber das Portal:</p>
<a class="knowledge-card-link" href="schadenfall.html">https://ludwigoelze.com/schadenfall</a>
<p>In den meisten F&auml;llen geht es &uuml;ber die spezielle Rechnungs-App der Gesellschaft noch schneller!</p>
<p>Bitte lies dir dort die Anweisungen genau durch, um eine schnelle Auszahlung der Leistung ohne viele R&uuml;ckfragen zu gew&auml;hrleisten. Auch wenn du noch nicht sicher bist, ob dein Schadenfall versichert ist, kann ich das &uuml;ber dieses Portal pr&uuml;fen.</p>
<p>Diese Meldungen werden priorisiert behandelt, damit dir schnell geholfen werden kann.</p>
</div>
<div class="card reveal stagger-3">
<div class="card-icon">${icon.file}</div>
<h3>3. KFZ-Anfragen und EVB-Nummern</h3>
<p>F&uuml;r KFZ-Anliegen sowie die Vergabe von EVB-Nummern steht dir ab sofort mein gesch&auml;tzter und erfahrener Kollege Charly Staab zur Verf&uuml;gung:</p>
<p>Kontakt:</p>
${list(['Staab &amp; Staab GmbH - Deine Versicherungsmakler', 'Karl-Theodor-Str. 47, 80803 M&uuml;nchen', 'Telefon: <a href="tel:+4989293689">089 29 36 89</a>', 'E-Mail: <a href="mailto:post@staab-versicherungsmakler.de">post@staab-versicherungsmakler.de</a>'])}
<p>Bitte setze mich in Kopie, damit ich &uuml;ber die Bearbeitung auf dem Laufenden bleibe.</p>
</div>
<div class="card reveal stagger-4">
<div class="card-icon">${icon.card}</div>
<h3>4. Adress&auml;nderungen, Kontaktdaten und Bankdaten</h3>
<p>Diese kannst du bequem &uuml;ber meine App aktualisieren. Solltest du die App noch nicht nutzen, sende ich dir den Link gerne zu.</p>
<p>Vertrags&auml;nderungen, die nicht oben genannte Punkte betreffen, erfordern eine Terminbuchung, da oft R&uuml;ckfragen entstehen k&ouml;nnen.</p>
</div>
<div class="card reveal stagger-5">
<div class="card-icon">${icon.globe}</div>
<h3>5. Kommunikation und Informationen</h3>
<p>Alle wichtigen Informationen werden regelm&auml;&szlig;ig auf meiner Webseite, per E-Mail und auf Instagram ver&ouml;ffentlicht. Folge mir gerne, um auf dem Laufenden zu bleiben!</p>
<p>Auch f&uuml;r die gesetzliche Krankenkasse wird es &Auml;nderungen geben, &uuml;ber die ich dich rechtzeitig informieren werde.</p>
</div>
<div class="card reveal stagger-6">
<div class="card-icon">${icon.check}</div>
<h3>6. Terminmanagement</h3>
<p>Solltest du einen gebuchten Termin verschieben oder absagen m&uuml;ssen, kannst du das einfach &uuml;ber den Link in deiner Terminbest&auml;tigung tun.</p>
</div>
<div class="card reveal stagger-1">
<h3>7. Baufinanzierung</h3>
<p>F&uuml;r alle Anliegen rund um Baufinanzierung steht dir ab sofort mein Kollege Kilian Hauns vor Ort und Online zur Verf&uuml;gung.</p>
<p>Er arbeitet als selbstst&auml;ndiger Baufinanzierungsexperte bei der LBS in B&uuml;hl. Dank seiner Position hat er den besten direkten Draht bei individuellen F&auml;llen. Zus&auml;tzlich nutzt er dieselbe Vergleichsplattform wie ich, wodurch er deutschlandweit identische Konditionen anbieten kann.</p>
<p>Kontakt:</p>
${list(['Kilian Hauns, Stv. Bezirksdirektor LBS', 'Beratungsstelle B&uuml;hl, Grabenstr. 11, 77815 B&uuml;hl', 'Telefon: <a href="tel:+4972238088631">07223/80886-31</a>', 'Mobil: <a href="tel:+491629590506">0162/9590506</a>', 'E-Mail: <a href="mailto:Kilian.Hauns@LBS-Sued.de">Kilian.Hauns@LBS-Sued.de</a>'])}
<p>Bitte setze mich ebenfalls in Kopie, damit ich &uuml;ber die Bearbeitung auf dem Laufenden bleibe</p>
</div>
<div class="card reveal stagger-2">
<h3>8. Strom- &amp; Gaspreis Vergleiche</h3>
<p>Strom- und Gasvergleiche werden ab sofort von meinem gesch&auml;tzten Kollegen Tino Stoll - Seefeldstr. 1 - 76437 Rastatt bearbeitet:</p>
<a class="btn btn-secondary btn-full" href="https://calendly.com/envervice_team/erstgespraech_tarifoptimierung" rel="noopener noreferrer" target="_blank">Termin f&uuml;r Energievergleich</a>
<p>Bitte sende deine aktuelle Rechnung nach der Buchung an ihn. Tino sorgt daf&uuml;r, dass jedes Jahr der g&uuml;nstigste und zuverl&auml;ssigste Energieanbieter f&uuml;r dich gefunden wird. Sein besonderes System ber&uuml;cksichtigt auch Grundversorger und legt gro&szlig;en Wert auf Datenschutz und die Solvenz der Anbieter.</p>
</div>
<div class="card reveal stagger-3">
<h3>9. Gesetzliche Krankenkassen</h3>
<p>M&ouml;chtest du deine gesetzliche Krankenkasse wechseln?</p>
<p>Ab sofort &uuml;bernehmen meine Kollegen Olaf Walkenhorst und Fiona Jasmut aus Hamburg diese Aufgabe sowie eine j&auml;hrliche &Uuml;berpr&uuml;fung f&uuml;r dich. Mit dem Tool „Kassenkompass“ findest du in wenigen Minuten die Krankenkasse, die am besten zu dir passt. Du kannst selbst entscheiden, was dir wichtig ist: Leistungen, Bonusprogramme oder der Preis.</p>
<p>Weitere Informationen findest du unter:</p>
<a class="btn btn-secondary btn-full" href="https://kassenkompass.de/bonusrechner/?lizenz=ludwigoelze" rel="noopener noreferrer" target="_blank">Kassenkompass &ouml;ffnen</a>
</div>
<div class="card reveal stagger-4">
<h3>10. Arbeitskraftabsicherung</h3>
<p>Die Absicherung deiner Arbeitskraft ist eines der wichtigsten Themen deiner Vorsorge. Um hier eine Beratung auf h&ouml;chstem Experten-Niveau und mit spezialisierter Begleitung im Leistungsfall zu garantieren, arbeite ich eng mit Marco und sein Team der buXperts GmbH zusammen.</p>
<p>Egal ob es um den Neuabschluss einer Berufsunf&auml;higkeitsversicherung, eine Grundf&auml;higkeitsabsicherung oder die Absicherung deines Einkommens via Krankentagegeld geht: Marco und sein Team sind die Spezialisten an deiner Seite.</p>
<p>Dein Vorteil: Du erh&auml;ltst Zugriff auf spezialisierte Risikovoranfragen und eine tiefe Expertise, die &uuml;ber die Standardberatung hinausgeht.</p>
<a class="btn btn-secondary btn-full" href="https://buxperts.de/kooperation-ludwig-oelze/" rel="noopener noreferrer" target="_blank">BU-Spezialberatung ansehen</a>
<p>Bitte setze mich auch hier bei E-Mail-Korrespondenz in Kopie, damit ich &uuml;ber deine Absicherungsstrategie informiert bleibe.</p>
</div>
</div>
</div>
</section>
<section class="section bg-off-white">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>Was bleibt wie gewohnt?</h2>
</div>
<div class="card reveal reveal-right">
${list(['Laufende Anfragen werden selbstverständlich weiterhin bearbeitet.', 'Angeforderte Unterlagen werden direkt an die Gesellschaften weitergeleitet und du wirst in Kopie gesetzt.', `Beratungstermine vor Ort und Online k&ouml;nnen weiterhin &uuml;ber <a href="${calendlyLinks.weitereFragen}" rel="noopener noreferrer" target="_blank">${calendlyLinks.weitereFragen}</a> gebucht werden.`])}
</div>
</div>
</div>
</section>
<section class="section bg-white">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>Warum dieses Pilotprojekt?</h2>
<p>In den letzten Jahren hat sich gezeigt, dass klare Strukturen und gezielte Kommunikation dazu beitragen, deine Anliegen schneller und besser zu bearbeiten. Dieses Projekt soll sicherstellen, dass du jederzeit wei&szlig;t, an wen du dich wenden kannst und wie deine Anfragen bearbeitet werden. Gleichzeitig kann ich meine Ressourcen optimal nutzen, um dir den besten Service zu bieten.</p>
<p>Vielen Dank f&uuml;r dein Vertrauen und deine Unterst&uuml;tzung bei diesem Pilotprojekt. Ich freue mich auf ein erfolgreiches Jahr mit dir!</p>
<p>Viele Gr&uuml;&szlig;e<br/>Ludwig Oelze</p>
</div>
<div class="card reveal reveal-right">
<h3>Durchblick</h3>
<p>&Uuml;brigens: Ich m&ouml;chte Dir bei der Gelegenheit Durchblick vorstellen - meine clevere Vorsorgel&ouml;sung, die Dir mehr aus Deinem Geld macht - sicher und flexibel:</p>
<a class="knowledge-card-link" href="durchblick.html">https://ludwigoelze.com/durchblick</a>
<p>Schau&rsquo;s Dir gerne mal an! Ich finde, das k&ouml;nnte auch f&uuml;r Dich interessant sein.</p>
</div>
</div>
<div class="cta-actions mt-8">
<a class="btn btn-secondary btn-lg" href="index.html">Zur&uuml;ck zur Startseite</a>
</div>
</div>
</section>
</main>`;
}

function unternehmerMain() {
  return `<main>
<section class="hero hero-small">
<div class="hero-bg"><img alt="Ludwig Oelze Beratung" loading="eager" src="Ludwig_prev_foto/_X8A3042_prev.webp"/></div>
<div class="hero-overlay" style="background: linear-gradient(135deg, rgba(8, 37, 37, 0.86) 0%, rgba(18, 64, 64, 0.78) 45%, rgba(201, 169, 98, 0.50) 100%);"></div>
<div class="hero-content">
<span class="hero-badge">${icon.file} Unternehmervollmacht</span>
<h1>Die <span class="highlight">Unternehmervollmacht</span>: rechtliche Absicherung f&uuml;r Unternehmer</h1>
<p class="hero-subtitle">Was passiert mit Deinem Unternehmen, wenn Du pl&ouml;tzlich nicht entscheiden kannst? Eine Unternehmervollmacht sichert Handlungsf&auml;higkeit, Vertretung und Verantwortung im Ernstfall.</p>
</div>
</section>
<section class="section bg-white">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>Warum Unternehmer anders vorsorgen m&uuml;ssen</h2>
<p>Als Unternehmer tr&auml;gst Du t&auml;glich Verantwortung f&uuml;r Kunden, Mitarbeiter, Vertr&auml;ge, Konten und laufende Entscheidungen. Ein Unfall, eine schwere Erkrankung oder eine l&auml;ngere Abwesenheit kann schnell zur echten Gefahr f&uuml;r den Betrieb werden.</p>
<p>Eine Unternehmervollmacht legt fest, wer in solchen Situationen handeln darf. Sie kann helfen, Liquidit&auml;t, Vertragsf&auml;higkeit, Personalentscheidungen und operative Abl&auml;ufe zu sichern, wenn Du selbst vor&uuml;bergehend oder dauerhaft ausf&auml;llst.</p>
</div>
<div class="card reveal reveal-right">
<h3>Worum es geht</h3>
${list(['Handlungsf&auml;higkeit bei Krankheit oder Unfall', 'Vertretung gegen&uuml;ber Banken, Kunden und Vertragspartnern', 'klare Befugnisse statt ungeklärter Zuständigkeiten', 'Absicherung des Unternehmens und der Familie'])}
</div>
</div>
</div>
</section>
<section class="section bg-off-white">
<div class="container">
<div class="section-header reveal"><h2>Bausteine einer sauberen Unternehmervollmacht</h2></div>
<div class="grid grid-4">
${card('Rechtliche Einordnung', 'Die Vollmacht sollte eindeutig regeln, wer handeln darf und in welchem Umfang Entscheidungen getroffen werden können.', icon.file, 'stagger-1')}
${card('Betriebssicherung', 'Ziel ist, dass Rechnungen, Löhne, Lieferanten, Kundenkommunikation und laufende Verträge nicht blockiert werden.', icon.shield, 'stagger-2')}
${card('Banken und Verträge', 'Gerade Kontozugriff, Darlehen, Mietverträge und wichtige Geschäftsdokumente brauchen klare Vertretungsregeln.', icon.card, 'stagger-3')}
${card('Widerruf und Kontrolle', 'Eine gute Vollmacht definiert nicht nur Befugnisse, sondern auch Grenzen, Dokumentation und Widerrufsmöglichkeiten.', icon.check, 'stagger-4')}
</div>
</div>
</section>
<section class="section bg-white">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>Was geregelt werden sollte</h2>
<p>Der Inhalt h&auml;ngt stark von Rechtsform, Unternehmensgr&ouml;&szlig;e, Gesellschafterstruktur und pers&ouml;nlicher Situation ab. Typisch sind Regelungen zu Bankgesch&auml;ften, Vertragsabschl&uuml;ssen, Personalfragen, Steuer- und Beh&ouml;rdenkommunikation, Versicherungen und organisatorischen Notfallprozessen.</p>
<p>Wichtig ist, dass die Vollmacht nicht isoliert betrachtet wird. Sie sollte zu Gesellschaftsvertrag, Vorsorgevollmacht, Patientenverf&uuml;gung, Testament, Nachfolgeplanung und Versicherungsstruktur passen.</p>
</div>
<div class="card reveal reveal-right">
<h3>Typische Befugnisse</h3>
${list(['Zahlungsverkehr und Bankkommunikation', 'Vertrags- und Lieferantenmanagement', 'Personal- und Organisationsfragen', 'Kommunikation mit Steuerberater, Behörden und Versicherern'], true)}
</div>
</div>
</div>
</section>
<section class="section bg-off-white">
<div class="container">
<div class="two-col">
<div class="reveal">
<h2>Form, Dokumentation und Zugriff</h2>
<p>Je nach Inhalt kann eine notarielle Beglaubigung oder Beurkundung sinnvoll oder erforderlich sein. Das gilt besonders, wenn Immobilien, Gesellschaftsanteile, Registerthemen oder weitreichende Bankbefugnisse betroffen sind.</p>
<p>Mindestens genauso wichtig ist die praktische Auffindbarkeit: Die bevollm&auml;chtigte Person muss wissen, wo Unterlagen, Passw&ouml;rter, Ansprechpartner und Notfallinformationen liegen. Sonst bleibt selbst eine gute Vollmacht im Ernstfall zu langsam.</p>
</div>
<div class="card reveal reveal-right">
<h3>Praktische Umsetzung</h3>
${list(['Vollmacht schriftlich und eindeutig formulieren', 'rechtliche Prüfung durch Notar oder Rechtsanwalt einplanen', 'Notfallordner und Ansprechpartnerliste pflegen', 'regelmäßig bei Veränderungen aktualisieren'])}
</div>
</div>
</div>
</section>
<section class="section bg-white">
<div class="container container-narrow">
<div class="section-header reveal"><h2>H&auml;ufige Fragen</h2></div>
${accordion([
  { q: 'Ist eine private Vorsorgevollmacht f&uuml;r Unternehmer ausreichend?', a: 'Oft nicht. Private Vorsorgevollmachten regeln persönliche Themen, decken aber betriebliche Befugnisse, Konten, Verträge und Gesellschaftsfragen nicht automatisch sauber ab.' },
  { q: 'Wer sollte bevollm&auml;chtigt werden?', a: 'Das sollte eine vertrauenswürdige, fachlich geeignete und erreichbare Person sein. Bei größeren Unternehmen kann auch eine abgestufte Lösung mit mehreren Zuständigkeiten sinnvoll sein.' },
  { q: 'Muss die Unternehmervollmacht notariell sein?', a: 'Das hängt vom Inhalt ab. Bei Immobilien, Gesellschaftsanteilen, Registerthemen und bestimmten Bankthemen ist notarielle Unterstützung häufig sinnvoll oder notwendig.' },
  { q: 'Wie oft sollte die Vollmacht geprüft werden?', a: 'Immer bei Änderungen der Rechtsform, Gesellschafterstruktur, Bankverbindungen, Familie, Nachfolgeplanung oder wesentlichen Verträgen. Ein regelmäßiger Check ist sinnvoll.' },
  { q: 'Gehört die Unternehmervollmacht zur Versicherungsberatung?', a: 'Sie ist kein Versicherungsprodukt, aber ein wichtiger Teil der Risikovorsorge. Ich kann den Bedarf einordnen und bei Bedarf an juristische Ansprechpartner verweisen.' },
])}
</div>
</section>
<section class="cta-section" id="contact">
<div class="container">
<h2 class="reveal">Unternehmerische Notfallplanung sauber aufsetzen</h2>
<p class="reveal">Wenn Du wissen willst, ob Dein Unternehmen im Ernstfall handlungsf&auml;hig bleibt, pr&uuml;fen wir gemeinsam die wichtigsten Risiken und Schnittstellen.</p>
<div class="cta-actions">
<a class="btn btn-primary btn-lg" href="kontakt.html">Beratungsgespr&auml;ch vereinbaren</a>
<a class="btn btn-secondary btn-lg" href="gold-service.html">Gold-Service ansehen</a>
</div>
</div>
</section>
</main>`;
}

function durchblickKnowledgeTeaser() {
  return `<section class="section bg-white">
<div class="container">
<div class="two-col">
<div class="reveal">
<span class="section-badge">Dokumente</span>
<h2>Durchblick</h2>
<p>Die Durchblick-Seite enth&auml;lt die kurze ETF-Vorsorge-Information, den Terminlink und die beiden PDF-Dokumente aus der bestehenden Seite.</p>
<div class="cta-actions">
<a class="btn btn-primary btn-lg" href="durchblick.html#dokumente">Dokumente &ouml;ffnen</a>
<a class="btn btn-secondary btn-lg" href="durchblick.html">Durchblick ansehen</a>
</div>
</div>
<a class="card knowledge-card reveal reveal-right" href="durchblick.html">
<div class="card-icon">${icon.card}</div>
<h3>Deine ETF-Vorsorge der neuen Generation</h3>
<p>Renditechancen, Steuervorteile, Flexibilit&auml;t und lebenslange Rente kurz erkl&auml;rt.</p>
<span class="knowledge-card-link">Zur Seite</span>
</a>
</div>
</div>
</section>`;
}

function wissenMain() {
  const items = [
    ['Berufsunf&auml;higkeit', 'Arbeitskraft absichern, Gesundheitsfragen vorbereiten und echte Leistungsausl&ouml;ser verstehen.', 'berufsunfaehigkeit.html'],
    ['Haftpflichtversicherung', 'Warum private Haftpflicht so wichtig ist und welche Zusatzbausteine wirklich zählen.', 'haftpflichtversicherung.html'],
    ['Hausratversicherung', 'Einrichtung, Wertsachen, Unterversicherung und Schadenfall sauber einordnen.', 'hausratversicherung.html'],
    ['Geb&auml;udeversicherung', 'Feuer, Leitungswasser, Sturm, Elementar und Sanierungspflichten im Blick behalten.', 'gebaeudeversicherung.html'],
    ['Rentenversicherung', 'Gesetzliche Rente, private Vorsorge und betriebliche Bausteine zusammen denken.', 'rentenversicherung.html'],
    ['Ank&uuml;ndigung', 'Aktuelle Mandanteninformation zu Servicewegen, Terminen, Schadenf&auml;llen und Spezialthemen.', 'ankuendigung.html'],
    ['Schadenfall', 'Schadenmeldung mit Angaben und Dateien direkt vorbereiten.', 'schadenfall.html'],
    ['Pflegeversicherung', 'Pflegel&uuml;cke, Pflegegrade und private Erg&auml;nzung realistisch planen.', 'pflegeversicherung.html'],
    ['Krankenversicherung', 'GKV, PKV, Tarifwechsel und internationale Anforderungen verstehen.', 'krankenversicherung.html'],
    ['Rechtsschutzversicherung', 'Bausteine, Wartezeiten und Leistungsfall ohne falsche Erwartungen einordnen.', 'rechtsschutzversicherung.html'],
    ['Zahnzusatzversicherung', 'Timing, laufende Behandlungen und Tarifauswahl vor der gro&szlig;en Rechnung klären.', 'zahnzusatzversicherung.html'],
    ['PassportCard', 'Internationale Krankenversicherung mit praktischer Kartenlogik f&uuml;r Expats und Nomaden.', 'passportcard.html'],
    ['Expat-Beratung', 'Dubai, Spanien und Deutschland mit einer sauberen Versicherungsstruktur verbinden.', 'expat-beratung-1.html'],
    ['Unternehmervollmacht', 'Handlungsf&auml;higkeit im Unternehmen sichern, wenn Du selbst ausfällst.', 'unternehmervollmacht.html'],
  ];

  return `<main>
<section class="hero hero-small">
<div class="hero-bg"><img src="Ludwig_prev_foto/_X8A3120_prev.webp" alt="Ludwig Oelze Beratung" loading="eager"></div>
<div class="hero-overlay"></div>
<div class="hero-content">
<span class="hero-badge">${icon.file} Wissen &amp; Ratgeber</span>
<h1>Verstehen statt <span class="highlight">vertrauen m&uuml;ssen</span></h1>
<p class="hero-subtitle">Hier findest Du die wichtigsten Themen als direkte Einstiege. Kein leeres Magazin, sondern ein Ratgeber-Hub zu den Seiten, die bereits wirklich ausgearbeitet sind.</p>
</div>
</section>
<section class="section bg-white">
<div class="container">
<div class="section-header reveal">
<h2>Themen, die Du direkt vertiefen kannst</h2>
<p>Wähle ein Thema und springe direkt zur passenden Seite.</p>
</div>
<div class="grid grid-3">
${items.map(([title, text, href], index) => `<a class="card knowledge-card reveal stagger-${(index % 6) + 1}" href="${href}">
<div class="card-icon">${index % 3 === 0 ? icon.shield : index % 3 === 1 ? icon.file : icon.globe}</div>
<h3>${title}</h3>
<p>${text}</p>
<span class="knowledge-card-link">Mehr erfahren</span>
</a>`).join('\n')}
</div>
</div>
</section>
<section class="section bg-off-white">
<div class="container">
<div class="section-header reveal">
<h2>H&auml;ufig gesuchte Einstiege</h2>
</div>
<div class="grid grid-3">
<a class="article-card reveal stagger-1" href="berufsunfaehigkeit.html">
<div class="article-card-image"><img src="https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=600&amp;q=80" alt="Berufsunfähigkeit" loading="lazy"></div>
<div class="article-card-content"><span class="article-card-category">Vorsorge</span><h3>Berufsunf&auml;higkeit: Arbeitskraft richtig absichern</h3><p class="article-card-meta">Zum Ratgeber</p></div>
</a>
<a class="article-card reveal stagger-2" href="unternehmervollmacht.html">
<div class="article-card-image"><img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=600&amp;q=80" alt="Unternehmervollmacht" loading="lazy"></div>
<div class="article-card-content"><span class="article-card-category">Unternehmer</span><h3>Unternehmervollmacht: handlungsfähig bleiben</h3><p class="article-card-meta">Zum Ratgeber</p></div>
</a>
<a class="article-card reveal stagger-3" href="krankenversicherung.html">
<div class="article-card-image"><img src="https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=600&amp;q=80" alt="Krankenversicherung" loading="lazy"></div>
<div class="article-card-content"><span class="article-card-category">Gesundheit</span><h3>Krankenversicherung: Entscheidungen richtig einordnen</h3><p class="article-card-meta">Zum Ratgeber</p></div>
</a>
</div>
</div>
</section>
<section class="cta-section">
<div class="container">
<h2 class="reveal">Noch Fragen?</h2>
<p class="reveal">Wenn Du nicht weißt, welcher Einstieg zu Deiner Situation passt, sortieren wir das gemeinsam in einem kurzen Gespräch.</p>
<a href="kontakt.html" class="btn btn-primary btn-lg btn-pulse reveal">Beratungsgespr&auml;ch vereinbaren</a>
</div>
</section>
</main>`;
}

function updateIndex() {
  let html = read('index.html');
  html = html.replace(/, und/g, ' und');
  html = html.replace(/Bankfachwirt \(IHK\)/g, 'Bankfachwirt');
  html = html
    .replace(/<a href="familien\.html" class="btn btn-primary mt-4">/g, '<a href="familien.html" target="_self" class="btn btn-primary mt-4">')
    .replace(/<a href="selbststaendige\.html" class="btn btn-primary mt-4">/g, '<a href="selbststaendige.html" target="_self" class="btn btn-primary mt-4">')
    .replace(/<a href="expats\.html" class="btn btn-primary mt-4">/g, '<a href="expats.html" target="_self" class="btn btn-primary mt-4">');
  html = html.replace(
    /href="https:\/\/ludwigoelze\.com\/ankuendigung"\s+target="_blank"\s+rel="noopener noreferrer"/g,
    'href="ankuendigung.html"'
  );
  html = html.replace(
    /<time id="hero-mandanten-stand"[^>]*>[\s\S]*?<\/time>/,
    `<time id="hero-mandanten-stand" data-current-month-stand data-current-month-prefix="Wichtige Infos - Stand" datetime="${currentStand.iso}">Wichtige Infos - Stand ${currentStand.label}</time>`
  );
  html = html.replace(
    /<div class="partner-logos mt-12 reveal">[\s\S]*?<\/div>\s*<\/div>\s*<\/section>\s*<!-- Testimonials Section -->/,
    `<div class="partner-logos mt-12 reveal">
                    <a href="https://www.allianz.de/" target="_blank" rel="noopener noreferrer" aria-label="Allianz"><img src="assets/images/partners/allianz.svg" alt="Allianz" loading="lazy"></a>
                    <a href="https://www.axa.de/" target="_blank" rel="noopener noreferrer" aria-label="AXA"><img src="assets/images/partners/axa.svg" alt="AXA" loading="lazy"></a>
                    <a href="https://www.hdi.de/" target="_blank" rel="noopener noreferrer" aria-label="HDI"><img src="assets/images/partners/hdi.svg" alt="HDI" loading="lazy"></a>
                    <a href="https://www.alte-leipziger.de/" target="_blank" rel="noopener noreferrer" aria-label="Alte Leipziger"><img src="assets/images/partners/alte-leipziger-hallesche.svg" alt="Alte Leipziger" loading="lazy"></a>
                    <a href="https://www.ergo.de/" target="_blank" rel="noopener noreferrer" aria-label="ERGO"><img src="assets/images/partners/ergo.webp" alt="ERGO" loading="lazy"></a>
                    <a href="https://www.arag.de/" target="_blank" rel="noopener noreferrer" aria-label="ARAG"><img src="assets/images/partners/arag.svg" alt="ARAG" loading="lazy"></a>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->`
  );
  html = html.replace(
    /<div class="google-badge">([\s\S]*?)<\/div>\s*<\/div>\s*<\/div>\s*<\/section>\s*<!-- CTA Section -->/,
    `<a class="google-badge" href="https://www.google.com/maps/search/?api=1&amp;query=Ludwig%20Oelze%20Bismarckstra%C3%9Fe%2026%2076530%20Baden-Baden" target="_blank" rel="noopener noreferrer" aria-label="Google Bewertungen von Ludwig Oelze ansehen">$1</a>
                </div>
            </div>
        </section>

        <!-- CTA Section -->`
  );
  html = html.replace(
    /<div class="flex justify-center gap-4 mt-8" style="flex-wrap: wrap;">\s*<a href="kontakt\.html" class="btn btn-primary btn-lg btn-pulse">Beratungsgespräch vereinbaren<\/a>\s*<a href="zusammenarbeit\.html" class="btn btn-white btn-lg">Erstmal mehr erfahren<\/a>\s*<\/div>/,
    `<div class="cta-actions mt-8">
                    <a href="kontakt.html" class="btn btn-primary btn-lg btn-pulse">Beratungsgespräch vereinbaren</a>
                    <a href="zusammenarbeit.html" class="btn btn-white btn-lg">Erstmal mehr erfahren</a>
                </div>`
  );
  write('index.html', cleanFooter(html));
}

function fixBerufsunfaehigkeit() {
  let html = read('berufsunfaehigkeit.html');
  html = html
    .replace(/<p>&copy; 2025 Ludwig Oelze Versicherungsmakler\. Alle Rechte vorbehalten\.<\/p>/g, '')
    .replace(/<p>Quellen:<\/p>/g, '<p class="source-note">Fachliche Orientierung: BU-Portal24, LV1871, Clark und Wikipedia wurden als Begriffsnachweise berücksichtigt.</p>');
  for (const source of [
    '<li><span>BU-Portal24 &ndash; Definition</span></li>',
    '<li><span>LV1871 &ndash; Berufsunf&auml;higkeitsversicherung</span></li>',
    '<li><span>Clark &ndash; Diagnose und Definition</span></li>',
    '<li><span>Wikipedia &ndash; Berufsunf&auml;higkeitsversicherung</span></li>',
  ]) {
    html = html.split(source).join('');
  }
  write('berufsunfaehigkeit.html', html);
}

function fixFeedbackCleanupPages() {
  let renten = read('rentenversicherung.html');
  renten = renten
    .replace(/<p>Quellen:<\/p>/g, '')
    .replace(/<li><span>Bundesministerium f&uuml;r Arbeit und Soziales \(BMAS\)<\/span><\/li>/g, '')
    .replace(/<li><span>Deutsche Rentenversicherung<\/span><\/li>/g, '')
    .replace(/<li><span>Bundeszentrale f&uuml;r politische Bildung<\/span><\/li>/g, '')
    .replace(/<li><span>Wikipedia - Gesetzliche Rentenversicherung<\/span><\/li>/g, '');
  write('rentenversicherung.html', renten);

  let haftpflicht = read('haftpflichtversicherung.html');
  haftpflicht = haftpflicht
    .replace(/<p>Quellen:<\/p>/g, '')
    .replace(/<li><span>Die Versicherer - Private Haftpflichtversicherung<\/span><\/li>/g, '')
    .replace(/<li><span>BaFin - Haftpflichtversicherung<\/span><\/li>/g, '')
    .replace(/<li><span>Verbraucherzentrale - Private Haftpflichtversicherung<\/span><\/li>/g, '')
    .replace(/<li><span>Wikipedia - Privathaftpflichtversicherung<\/span><\/li>/g, '');
  write('haftpflichtversicherung.html', haftpflicht);

  let hausrat = read('hausratversicherung.html');
  hausrat = hausrat
    .replace(/<li><span>Pers&ouml;nliche Beratungsgespr&auml;che in meinem B&uuml;ro in der Rheinstra&szlig;e 80 in Baden-Baden<\/span><\/li>/g, '<li><span>Pers&ouml;nliche Beratungsgespr&auml;che nach Terminvereinbarung</span></li>')
    .replace(/<li><span>Adresse: Rheinstra&szlig;e 80, 76532 Baden-Baden<\/span><\/li>/g, '');
  write('hausratversicherung.html', hausrat);

  let kranken = read('krankenversicherung.html');
  kranken = kranken.replace(
    /<h1>Bei der <span class="highlight">Krankenversicherung<\/span> geht es nie nur um Beitrag, sondern immer auch um Richtung<\/h1>/,
    '<h1>Private Krankenversicherung: <span class="highlight">Premiumschutz f&uuml;r Ihre Gesundheit</span></h1>'
  );
  write('krankenversicherung.html', kranken);

  let ueber = read('ueber-ludwig.html');
  ueber = ueber
    .replace(/<h3>Bankfachwirt<\/h3>/g, '<h3>Bankfachwirt (SBW)</h3>')
    .replace(/Bankfachwirt, freier Versicherungsmakler/g, 'Bankfachwirt (SBW), freier Versicherungsmakler');
  write('ueber-ludwig.html', ueber);

  let kontakt = read('kontakt.html');
  kontakt = kontakt
    .replace(
      /<a href="https:\/\/calendly\.com\/einsparung\/zusammenarbeit" target="_blank" rel="noopener noreferrer"\s*class="btn btn-secondary btn-sm">\+49 176 43689181<\/a>/,
      '<a href="tel:+4917643689181" class="btn btn-secondary btn-sm">+49 176 43689181</a>'
    )
    .replace(
      /<a href="https:\/\/calendly\.com\/einsparung\/zusammenarbeit" target="_blank" rel="noopener noreferrer"\s*class="btn btn-secondary btn-sm">Termin buchen<\/a>/,
      `<a href="${calendlyLinks.telefon}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-sm">Termin buchen</a>`
    );
  write('kontakt.html', kontakt);
}

function updateMapping() {
  const mapping = JSON.parse(read('leadwerk_importer/manifest/mapping.json'));
  mapping.pages = mapping.pages.filter((page) => !['teilnahmebedingungen.html', 'vorgangsabfrage.html'].includes(page.source_file));
  if (!mapping.pages.some((page) => page.source_file === 'unternehmervollmacht.html')) {
    const insertAfter = mapping.pages.findIndex((page) => page.source_file === 'gold-service.html');
    const page = {
      source_key: 'ludwig-unternehmervollmacht-v1',
      field_name: 'ludwig_page_document',
      source_file: 'unternehmervollmacht.html',
      title: 'Unternehmervollmacht',
      slug: 'unternehmervollmacht',
      target_type: 'page',
      post_status: 'publish',
      focus_keyphrase: 'Unternehmervollmacht',
      focus_keyphrase_en: 'business power of attorney',
    };
    mapping.pages.splice(insertAfter + 1, 0, page);
  }
  if (!mapping.pages.some((page) => page.source_file === 'durchblick.html')) {
    const insertAfter = mapping.pages.findIndex((page) => page.source_file === 'unternehmervollmacht.html');
    const page = {
      source_key: 'ludwig-durchblick-v1',
      field_name: 'ludwig_page_document',
      source_file: 'durchblick.html',
      title: 'Durchblick ETF-Vorsorge',
      slug: 'durchblick',
      target_type: 'page',
      post_status: 'publish',
      focus_keyphrase: 'Durchblick ETF-Vorsorge',
      focus_keyphrase_en: 'Durchblick ETF pension',
    };
    mapping.pages.splice(insertAfter + 1, 0, page);
  }
  if (!mapping.pages.some((page) => page.source_file === 'ankuendigung.html')) {
    const insertAfter = mapping.pages.findIndex((page) => page.source_file === 'durchblick.html');
    const page = {
      source_key: 'ludwig-ankuendigung-v1',
      field_name: 'ludwig_page_document',
      source_file: 'ankuendigung.html',
      title: 'Ankündigung',
      slug: 'ankuendigung',
      target_type: 'page',
      post_status: 'publish',
      focus_keyphrase: 'Ankündigung Kundenservice',
      focus_keyphrase_en: 'customer service announcement',
    };
    mapping.pages.splice(insertAfter + 1, 0, page);
  }
  if (!mapping.pages.some((page) => page.source_file === 'schadenfall.html')) {
    const insertAfter = mapping.pages.findIndex((page) => page.source_file === 'ankuendigung.html');
    const page = {
      source_key: 'ludwig-schadenfall-v1',
      field_name: 'ludwig_page_document',
      source_file: 'schadenfall.html',
      title: 'Schadenfall',
      slug: 'schadenfall',
      target_type: 'page',
      post_status: 'publish',
      focus_keyphrase: 'Schadenfall',
      focus_keyphrase_en: 'claim report',
    };
    mapping.pages.splice(insertAfter + 1, 0, page);
  }
  for (const page of mapping.pages) {
    if (page.source_file === 'durchblick.html') {
      page.title = 'Durchblick ETF-Vorsorge';
      page.focus_keyphrase = 'Durchblick ETF-Vorsorge';
      page.focus_keyphrase_en = 'Durchblick ETF pension';
    }
    if (page.source_file === 'ankuendigung.html') {
      page.title = 'Ankündigung';
      page.focus_keyphrase = 'Ankündigung Kundenservice';
      page.focus_keyphrase_en = 'customer service announcement';
    }
    if (page.source_file === 'schadenfall.html') {
      page.title = 'Schadenfall';
      page.focus_keyphrase = 'Schadenfall';
      page.focus_keyphrase_en = 'claim report';
    }
    if (page.source_file === 'passportcard-en.html') {
      page.import_languages = ['de'];
    }
  }
  write('leadwerk_importer/manifest/mapping.json', JSON.stringify(mapping, null, 2) + '\n');
}

for (const file of listRootHtml()) {
  write(file, cleanFooter(read(file)));
}

for (const file of migratedPages) {
  let html = read(file);
  html = removeWorumSection(html);
  html = cleanMigrationWording(html);
  write(file, cleanFooter(html));
}

updateIndex();
fixBerufsunfaehigkeit();
fixFeedbackCleanupPages();
replaceMain('wissen.html', wissenMain(), {
  title: 'Wissen & Ratgeber, Ludwig Oelze',
  description: 'Ratgeber-Hub von Ludwig Oelze mit direkten Einstiegen zu Versicherung, Vorsorge, Ausland und Unternehmervollmacht.',
  bodyClass: 'page-wissen',
});
replaceMain('passportcard.html', passportCardDeMain(), {
  title: 'PassportCard, Ludwig Oelze | Auslandskrankenversicherung ohne Vorkasse',
  description: 'PassportCard erklärt: internationale Krankenversicherung mit Kartenlogik, 24/7 Service und weniger klassischer Vorkasse für Expats und digitale Nomaden.',
  bodyClass: 'page-passportcard page-migrated-rich',
  lang: 'de',
});
replaceMain('passportcard-en.html', passportCardEnMain(), {
  title: 'PassportCard Dubai, Ludwig Oelze | Expat Health Insurance',
  description: 'PassportCard for Dubai expats: international health insurance with card-based medical payment, 24/7 service and practical support abroad.',
  bodyClass: 'page-passportcard-en page-migrated-rich',
  lang: 'de',
});
replaceMain('freelancer-nomaden.html', freelancerNomadenMain(), {
  title: 'Krankenversicherung f&uuml;r Selbst&auml;ndige in Spanien mit Dubai-Aufenthalten, Ludwig Oelze',
  description: 'Krankenversicherung f&uuml;r deutsche Selbst&auml;ndige in Spanien mit Dubai-Aufenthalten: spezialisierte Beratung f&uuml;r digitale Nomaden, Freelancer und mobile Unternehmer.',
  bodyClass: 'page-freelancer-nomaden page-migrated-rich',
  lang: 'de',
});
replaceMain('immobilien-nomaden.html', immobilienNomadenMain(), {
  title: 'Krankenversicherung f&uuml;r Immobilien-Unternehmer, Ludwig Oelze',
  description: 'Krankenversicherung f&uuml;r Immobilien-Unternehmer in Deutschland, Spanien und Dubai: spezialisierte Beratung f&uuml;r Multi-Location-Unternehmer.',
  bodyClass: 'page-immobilien-nomaden page-migrated-rich',
  lang: 'de',
});
replaceMain('digitale-nomaden.html', digitaleNomadenMain(), {
  title: 'Krankenversicherung f&uuml;r deutsche digitale Nomaden, Ludwig Oelze',
  description: 'Krankenversicherung f&uuml;r deutsche digitale Nomaden in Spanien und Dubai: spezialisierte Beratung f&uuml;r Remote Worker und ortsunabh&auml;ngige Unternehmer.',
  bodyClass: 'page-digitale-nomaden page-migrated-rich',
  lang: 'de',
});
replaceMain('expat-beratung-1.html', expatBeratungMain(), {
  title: 'Krankenversicherung f&uuml;r deutsche Expats in Dubai, Ludwig Oelze',
  description: 'Krankenversicherung f&uuml;r deutsche Expats in Dubai und den VAE: spezialisierte Beratung f&uuml;r lokale und internationale Absicherung.',
  bodyClass: 'page-expat-beratung page-migrated-rich',
  lang: 'de',
});

const template = read('passportcard.html');
write('durchblick.html', template);
replaceMain('durchblick.html', durchblickMain(), {
  title: 'Durchblick ETF-Vorsorge, Ludwig Oelze',
  description: 'Durchblick ETF-Vorsorge mit flexibler Vorsorge, Kostenvergleich, Produktinformationen und Beratung durch Ludwig Oelze.',
  bodyClass: 'page-durchblick page-migrated-rich',
  lang: 'de',
});
write('ankuendigung.html', template);
replaceMain('ankuendigung.html', ankuendigungMain(), {
  title: 'Ank&uuml;ndigung, Ludwig Oelze | Pilotprojekt Kundenservice',
  description: 'Aktuelle Mandanteninformation von Ludwig Oelze: neue Servicewege, Terminabl&auml;ufe, Schadenportal und spezialisierte Ansprechpartner.',
  bodyClass: 'page-ankuendigung page-migrated-rich',
  lang: 'de',
});
write('schadenfall.html', template);
replaceMain('schadenfall.html', schadenfallMain(), {
  title: 'Schadenfall, Ludwig Oelze',
  description: 'Schadenmeldung bei Ludwig Oelze mit Angaben zum Versicherungsnehmer, E-Mail, gesch&auml;tzter Schadenh&ouml;he und Dateiupload.',
  bodyClass: 'page-schadenfall page-migrated-rich',
  lang: 'de',
});
write('unternehmervollmacht.html', template);
replaceMain('unternehmervollmacht.html', unternehmerMain(), {
  title: 'Unternehmervollmacht, Ludwig Oelze | Rechtliche Absicherung f&uuml;r Unternehmer',
  description: 'Unternehmervollmacht erklärt: Handlungsfähigkeit, Vertretung, Notfallplanung und rechtliche Absicherung für Unternehmer.',
  bodyClass: 'page-unternehmervollmacht page-migrated-rich',
  lang: 'de',
});

replaceMain('impressum.html', impressumMain(), {
  title: 'Impressum / Kontakt, Ludwig Oelze',
  description: 'Impressum und Kontakt von Ludwig Oelze.',
  bodyClass: 'page-impressum',
  lang: 'de',
});
replaceMain('datenschutz.html', datenschutzMain(), {
  title: 'Datenschutzerkl&auml;rung, Ludwig Oelze',
  description: 'Datenschutzerkl&auml;rung von Ludwig Oelze.',
  bodyClass: 'page-datenschutz',
  lang: 'de',
});
replaceMain('erstinformation.html', erstinformationMain(), {
  title: 'Erstinformation, Ludwig Oelze',
  description: 'Erstinformation von Ludwig Oelze.',
  bodyClass: 'page-erstinformation',
  lang: 'de',
});

for (const file of listRootHtml()) {
  write(file, finalUserFacingCopyCleanup(read(file)));
}

updateMapping();

console.log('Ludwig feedback cleanup applied.');
