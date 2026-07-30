-- ============================================================
-- IMPORTAÇÃO DE MÁSCARAS TC — MASCARASTC.docx
-- Gerado automaticamente em 2026-07-30
-- Compatível com MariaDB 5.7 / MySQL 5.7
-- ATENÇÃO: Execute APÓS rodar as migrations principais
-- Substitua :TENANT_ID pelo ID real do tenant antes de executar
-- Substitua :USER_ID pelo ID real do médico (ou NULL para biblioteca)
-- ============================================================

-- Variáveis de configuração (ajuste antes de executar)
SET @tenant_id = 1;   -- Substitua pelo tenant_id correto
SET @user_id   = NULL; -- NULL = template de biblioteca; ou ID do médico
SET @publico   = 1;   -- 1 = visível para todos os médicos do tenant

-- Evitar duplicatas: só insere se não existir template com mesmo nome no tenant

-- [001] Angiotomografia Computadorizada da Aorta Abdominal, da Veia Cava Inferior, e Art
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada da Aorta Abdominal, da Veia Cava Inferior, e Arterial e Venosa dos Membros Inferiores',
    'TC',
    'Aorta abdominal, artérias ilíacas comuns, externas e internas prévias, sem alterações significativas de calibre.
Tronco celíaco, artérias mesentéricas superior e inferior prévias e com calibre preservado.
Artérias renais únicas, prévias e com calibre preservado.
Veias cava inferior, ilíacas comuns, internas e externas pérvias, de calibre e trajeto preservados.
Membro inferior DIREITO:
Angio ARTERIAL:
Artérias femorais comum, superficial e profunda pérvias, com calibre preservado.
Artéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior pérvias, sem alterações significativas de calibre.
Angio VENOSA:
Veias femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.
Membro inferior ESQUERDO:
Angio ARTERIAL:
Artérias femorais comum, superficial e profunda pérvias, com calibre preservado.
Artéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior pérvias, sem alterações significativas de calibre.
Angio VENOSA:
Veias femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Aorta abdominal, artérias ilíacas comuns, externas e internas prévias, sem alterações significativas de calibre.\\nTronco celíaco, artérias mesentéricas superior e inferior prévias e com calibre preservado.\\nArtérias renais únicas, prévias e com calibre preservado.\\nVeias cava inferior, ilíacas comuns, internas e externas pérvias, de calibre e trajeto preservados.\\nMembro inferior DIREITO:\\nAngio ARTERIAL:\\nArtérias femorais comum, superficial e profunda pérvias, com calibre preservado.\\nArtéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior pérvias, sem alterações significativas de calibre.\\nAngio VENOSA:\\nVeias femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.\\nMembro inferior ESQUERDO:\\nAngio ARTERIAL:\\nArtérias femorais comum, superficial e profunda pérvias, com calibre preservado.\\nArtéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior pérvias, sem alterações significativas de calibre.\\nAngio VENOSA:\\nVeias femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.", "impressao": "Exame sem alterações significativas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada da Aorta Abdominal, da Veia Cava Inferior, e Arterial e Venosa dos Membros Inferiores'
    AND ativo = 1
);

-- [002] Angiotomografia Computadorizada da Aorta Abdominal
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada da Aorta Abdominal',
    'TC',
    'Aorta abdominal pérvia, com trajeto e calibre preservados.
Tronco celíaco, artérias mesentéricas superior e inferior pérvias e com calibre preservado.
Artérias renais únicas, pérvias e com calibre preservado.
Artérias ilíacas comuns, externas e internas, femorais comuns e segmento proximal das femorais superficiais e profundas pérvios, sem alterações significativas de calibre.
Ateromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.
*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.
*** Aneurisma fusiforme da aorta abdominal que se estende inferiormente até o plano da bifurcação aórtica. Emergem do aneurisma as artérias XXXXX, que se apresentam pérvias e com calibre habitual. Medidas relevantes descritas abaixo.
*** Nota-se ainda, trombose mural na ao longo da luz do segmento dilatado.
*** Controle pós-operatório de correção de aneurisma da aorta abdominal com colocação de prótese, sem sinais de extravasamentos. Não são identificadas coleções no leito cirúrgico.
Diâmetros máximos da aorta:
- Transição toracoabdominal: XXX cm
- Aorta abdominal suprarrenal: XXX cm
- Aorta abdominal infrarrenal: XXX cm
/// INICIO DAS MEDIDAS CASO TENHA ANEURISMA AAAIR (INCLUIR AS ABAIXO para ancoragem do stent)
- Altura da emergência das artérias renais: XXX cm
- Aspecto cranial do segmento infra-renal: XXX cm
- Diâmetro máximo do saco aneurismático: XXX cm
- Extensão do aneurisma: XXX cm
- Extensão da aorta infra-renal: CASO_TENHA_ANEURISMA////
/// FIM DAS MEDIDAS CASO TENHA ANEURISMA DE AAAIR
- Bifurcação aórtica: XXX cm
- Artéria ilíaca comum direita pré bifurcação: XXXXX cm de diâmetro e XXX cm de extensão
- Artéria ilíaca externa direita : XXX cm
- Artéria ilíaca comum esquerda pré bifurcação: XXX cm de diâmetro e XXX cm de extensão
- Artéria ilíaca externa esquerda : XXX cm',
    '{"indicacao": "", "tecnica": "Exame realizado com aquisição multislice, antes e após a injeção intravenosa do meio de contraste iodado.", "achados": "Aorta abdominal pérvia, com trajeto e calibre preservados.\\nTronco celíaco, artérias mesentéricas superior e inferior pérvias e com calibre preservado.\\nArtérias renais únicas, pérvias e com calibre preservado.\\nArtérias ilíacas comuns, externas e internas, femorais comuns e segmento proximal das femorais superficiais e profundas pérvios, sem alterações significativas de calibre.\\nAteromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.\\n*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.\\n*** Aneurisma fusiforme da aorta abdominal que se estende inferiormente até o plano da bifurcação aórtica. Emergem do aneurisma as artérias XXXXX, que se apresentam pérvias e com calibre habitual. Medidas relevantes descritas abaixo.\\n*** Nota-se ainda, trombose mural na ao longo da luz do segmento dilatado.\\n*** Controle pós-operatório de correção de aneurisma da aorta abdominal com colocação de prótese, sem sinais de extravasamentos. Não são identificadas coleções no leito cirúrgico.\\nDiâmetros máximos da aorta:\\n- Transição toracoabdominal: XXX cm\\n- Aorta abdominal suprarrenal: XXX cm\\n- Aorta abdominal infrarrenal: XXX cm\\n/// INICIO DAS MEDIDAS CASO TENHA ANEURISMA AAAIR (INCLUIR AS ABAIXO para ancoragem do stent)\\n- Altura da emergência das artérias renais: XXX cm\\n- Aspecto cranial do segmento infra-renal: XXX cm\\n- Diâmetro máximo do saco aneurismático: XXX cm\\n- Extensão do aneurisma: XXX cm\\n- Extensão da aorta infra-renal: CASO_TENHA_ANEURISMA////\\n/// FIM DAS MEDIDAS CASO TENHA ANEURISMA DE AAAIR\\n- Bifurcação aórtica: XXX cm\\n- Artéria ilíaca comum direita pré bifurcação: XXXXX cm de diâmetro e XXX cm de extensão\\n- Artéria ilíaca externa direita : XXX cm\\n- Artéria ilíaca comum esquerda pré bifurcação: XXX cm de diâmetro e XXX cm de extensão\\n- Artéria ilíaca externa esquerda : XXX cm", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada da Aorta Abdominal'
    AND ativo = 1
);

-- [003] Angiotomografia Computadorizada da Aorta Abdominal e Artérias Ilíacas
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada da Aorta Abdominal e Artérias Ilíacas',
    'TC',
    'Aorta abdominal pérvia, com trajeto e calibre preservados.
Tronco celíaco, artérias mesentéricas superior e inferior pérvias e com calibre preservado.
Artérias renais únicas, pérvias e com calibre preservado.
Artérias ilíacas comuns, externas e internas, femorais comuns e segmento proximal das femorais superficiais e profundas pérvios, sem alterações significativas de calibre.
Ateromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.
*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.
*** Aneurisma fusiforme da aorta abdominal que se estende inferiormente até o plano da bifurcação aórtica. Emergem do aneurisma as artérias XXXXX, que se apresentam pérvias e com calibre habitual. Medidas relevantes descritas abaixo.
*** Nota-se ainda, trombose mural na ao longo da luz do segmento dilatado.
*** Controle pós-operatório de correção de aneurisma da aorta abdominal com colocação de prótese, sem sinais de extravasamentos. Não são identificadas coleções no leito cirúrgico.
Diâmetros máximos da aorta:
- Transição toracoabdominal: XXX cm
- Aorta abdominal suprarrenal: XXX cm
- Aorta abdominal infrarrenal: XXX cm
/// INICIO DAS MEDIDAS CASO TENHA ANEURISMA AAAIR (INCLUIR AS ABAIXO para ancoragem do stent)
- Altura da emergência das artérias renais: XXX cm
- Aspecto cranial do segmento infra-renal: XXX cm
- Diâmetro máximo do saco aneurismático: XXX cm
- Extensão do aneurisma: XXX cm
- Extensão da aorta infra-renal: CASO_TENHA_ANEURISMA////
/// FIM DAS MEDIDAS CASO TENHA ANEURISMA DE AAAIR
- Bifurcação aórtica: XXX cm
- Artéria ilíaca comum direita pré bifurcação: XXXXX cm de diâmetro e XXX cm de extensão
- Artéria ilíaca externa direita : XXX cm
- Artéria ilíaca comum esquerda pré bifurcação: XXX cm de diâmetro e XXX cm de extensão
- Artéria ilíaca externa esquerda : XXX cm',
    '{"indicacao": "", "tecnica": "Exame realizado com aquisição multislice, antes e após a injeção intravenosa do meio de contraste iodado.", "achados": "Aorta abdominal pérvia, com trajeto e calibre preservados.\\nTronco celíaco, artérias mesentéricas superior e inferior pérvias e com calibre preservado.\\nArtérias renais únicas, pérvias e com calibre preservado.\\nArtérias ilíacas comuns, externas e internas, femorais comuns e segmento proximal das femorais superficiais e profundas pérvios, sem alterações significativas de calibre.\\nAteromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.\\n*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.\\n*** Aneurisma fusiforme da aorta abdominal que se estende inferiormente até o plano da bifurcação aórtica. Emergem do aneurisma as artérias XXXXX, que se apresentam pérvias e com calibre habitual. Medidas relevantes descritas abaixo.\\n*** Nota-se ainda, trombose mural na ao longo da luz do segmento dilatado.\\n*** Controle pós-operatório de correção de aneurisma da aorta abdominal com colocação de prótese, sem sinais de extravasamentos. Não são identificadas coleções no leito cirúrgico.\\nDiâmetros máximos da aorta:\\n- Transição toracoabdominal: XXX cm\\n- Aorta abdominal suprarrenal: XXX cm\\n- Aorta abdominal infrarrenal: XXX cm\\n/// INICIO DAS MEDIDAS CASO TENHA ANEURISMA AAAIR (INCLUIR AS ABAIXO para ancoragem do stent)\\n- Altura da emergência das artérias renais: XXX cm\\n- Aspecto cranial do segmento infra-renal: XXX cm\\n- Diâmetro máximo do saco aneurismático: XXX cm\\n- Extensão do aneurisma: XXX cm\\n- Extensão da aorta infra-renal: CASO_TENHA_ANEURISMA////\\n/// FIM DAS MEDIDAS CASO TENHA ANEURISMA DE AAAIR\\n- Bifurcação aórtica: XXX cm\\n- Artéria ilíaca comum direita pré bifurcação: XXXXX cm de diâmetro e XXX cm de extensão\\n- Artéria ilíaca externa direita : XXX cm\\n- Artéria ilíaca comum esquerda pré bifurcação: XXX cm de diâmetro e XXX cm de extensão\\n- Artéria ilíaca externa esquerda : XXX cm", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada da Aorta Abdominal e Artérias Ilíacas'
    AND ativo = 1
);

-- [004] Angiotomografia Computadorizada da Aorta Abdominal e das Artérias dos Membros In
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada da Aorta Abdominal e das Artérias dos Membros Inferiores',
    'TC',
    '#### Sinais de ateromatose difusa, caracterizados por espessamento parietal e placas parcialmente calcificadas esparsas pelo leito estudado.
##### Associam-se irregularidades da superfície luminal, indicativas de ulcerações.
Aorta abdominal pérvia, com trajeto e calibre preservados.
Tronco celíaco pérvio, com trajeto e calibre preservados.
Artérias mesentéricas superior e inferior pérvias e com calibre preservado.
Artérias renais únicas, pérvias e com calibre preservado.
Artérias ilíacas comuns, externas e internas pérvias, sem alterações significativas de calibre.
Membro inferior direito:
Artérias femorais comum, superficial e profunda pérvias, com calibre preservado.
Artéria poplítea pérvia, com trajeto e calibre preservados.
Tronco tíbio-fibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior pérvios, sem alterações significativas de calibre.
Membro inferior esquerdo:
Artérias femorais comum, superficial e profunda pérvias, com calibre preservado.
Artéria poplítea pérvia, com trajeto e calibre preservados.
Tronco tíbio-fibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior pérvios, sem alterações significativas de calibre.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "#### Sinais de ateromatose difusa, caracterizados por espessamento parietal e placas parcialmente calcificadas esparsas pelo leito estudado.\\n##### Associam-se irregularidades da superfície luminal, indicativas de ulcerações.\\nAorta abdominal pérvia, com trajeto e calibre preservados.\\nTronco celíaco pérvio, com trajeto e calibre preservados.\\nArtérias mesentéricas superior e inferior pérvias e com calibre preservado.\\nArtérias renais únicas, pérvias e com calibre preservado.\\nArtérias ilíacas comuns, externas e internas pérvias, sem alterações significativas de calibre.\\nMembro inferior direito:\\nArtérias femorais comum, superficial e profunda pérvias, com calibre preservado.\\nArtéria poplítea pérvia, com trajeto e calibre preservados.\\nTronco tíbio-fibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior pérvios, sem alterações significativas de calibre.\\nMembro inferior esquerdo:\\nArtérias femorais comum, superficial e profunda pérvias, com calibre preservado.\\nArtéria poplítea pérvia, com trajeto e calibre preservados.\\nTronco tíbio-fibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior pérvios, sem alterações significativas de calibre.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada da Aorta Abdominal e das Artérias dos Membros Inferiores'
    AND ativo = 1
);

-- [005] Angiotomografia Computadorizada da Aorta Torácica
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada da Aorta Torácica',
    'TC',
    'Aorta torácica de contornos regulares, com trajeto e calibre preservados. Não há evidências de aneurismas ou dissecções.
Ramos supra-aórticos com trajeto e calibre preservados.
XXXXX Ateromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.
XXXXX Notam-se irregularidades da superfície luminal, indicativas de ulcerações.
XXXXX Aneurisma fusiforme da aorta torácica, que se inicia num plano cerca de _____ cm e se estende por cerca de _____ cm. O calibre máximo do aneurisma é de _____ cm. O calibre da aorta superiormente à dilatação é de .... cm e distalmente de _____ cm.
XXXXX Nota-se ainda, trombose mural na ao longo da luz do segmento dilatado.
XXXXX Controle pós-operatório de correção endovascular de aneurisma da aorta torácica, com colocação de endoprótese metálica. Endoprótese com posicionamento habitual e sem sinais de extravasamentos do meio de contraste. O diâmetro máximo do saco aneurismático é de ____cm.
XXXXX Controle pós-operatório de correção de aneurisma da aorta  torácica com colocação de prótese, sem sinais de extravasamentos. Não são identificadas coleções no leito cirúrgico.
Diâmetros máximos da aorta:
Seio de Valsalva: XXX cm
Aorta ascendente: XXX cm
Crossa da aorta: XXX cm
Aorta descendente: XXX cm
Transição toracoabdominal: XXX cm',
    '{"indicacao": "", "tecnica": "Exame realizado com aquisição multislice, antes e após a injeção intravenosa do meio de contraste iodado.", "achados": "Aorta torácica de contornos regulares, com trajeto e calibre preservados. Não há evidências de aneurismas ou dissecções.\\nRamos supra-aórticos com trajeto e calibre preservados.\\nXXXXX Ateromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.\\nXXXXX Notam-se irregularidades da superfície luminal, indicativas de ulcerações.\\nXXXXX Aneurisma fusiforme da aorta torácica, que se inicia num plano cerca de _____ cm e se estende por cerca de _____ cm. O calibre máximo do aneurisma é de _____ cm. O calibre da aorta superiormente à dilatação é de .... cm e distalmente de _____ cm.\\nXXXXX Nota-se ainda, trombose mural na ao longo da luz do segmento dilatado.\\nXXXXX Controle pós-operatório de correção endovascular de aneurisma da aorta torácica, com colocação de endoprótese metálica. Endoprótese com posicionamento habitual e sem sinais de extravasamentos do meio de contraste. O diâmetro máximo do saco aneurismático é de ____cm.\\nXXXXX Controle pós-operatório de correção de aneurisma da aorta  torácica com colocação de prótese, sem sinais de extravasamentos. Não são identificadas coleções no leito cirúrgico.\\nDiâmetros máximos da aorta:\\nSeio de Valsalva: XXX cm\\nAorta ascendente: XXX cm\\nCrossa da aorta: XXX cm\\nAorta descendente: XXX cm\\nTransição toracoabdominal: XXX cm", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada da Aorta Torácica'
    AND ativo = 1
);

-- [006] Angiotomografia Computadorizada da Aorta Torácica e Abdominal
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada da Aorta Torácica e Abdominal',
    'TC',
    'Ramos supra-aórticos com trajeto e calibre preservados.
Aorta torácica e abdominal de contornos regulares, com trajeto e calibre preservados. Não há evidências de aneurismas ou dissecções.
Tronco celíaco, artérias mesentéricas superior e inferior pérvias e com calibre preservado.
Artérias renais únicas, pérvias e com calibre preservado.
Artérias ilíacas comuns, externas e internas, femorais comuns e segmento proximal das femorais superficiais e profundas pérvios, sem alterações significativas de calibre.
Ateromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.
*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.
*** Aneurisma fusiforme da aorta abdominal que se estende inferiormente até o plano da bifurcação aórtica. Emergem do aneurisma as artérias XXXXX, que se apresentam pérvias e com calibre habitual. Medidas relevantes descritas abaixo.
*** Aneurisma fusiforme da aorta torácica, que se inicia num plano cerca de .... cm e se estende por cerca de ..... cm. O calibre máximo do aneurisma é de ...... cm. O calibre da aorta superiormente à dilatação é de .... cm e distalmente de ..... cm.
Nota-se ainda, trombose mural na ao longo da luz do segmento dilatado.
*** Controle pós-operatório de correção endovascular de aneurisma da aorta torácica, com colocação de endoprótese metálica. Endoprótese com posicionamento habitual e sem sinais de extravasamentos do meio de contraste. O diâmetro máximo do saco aneurismático é de ...........cm.
*** Controle pós-operatório de correção de aneurisma da aorta  torácica com colocação de prótese, sem sinais de extravasamentos. Não são identificadas coleções no leito cirúrgico.
Diâmetros máximos da aorta:
Seio de Valsalva: XXX cm
Aorta ascendente: XXX cm
Crossa da aorta: XXX cm
Aorta descendente: XXX cm
Transição toracoabdominal: XXX cm
Aorta abdominal suprarrenal: XXX cm
Aorta abdominal infrarrenal: XXX cm
/// INÍCIO DAS MEDIDAS CASO TENHA ANEURISMA AAAIR (INCLUIR AS ABAIXO para ancoragem do stent)
Altura da emergência das artérias renais: XXX cm
Aspecto cranial do segmento infra-renal: XXX cm
Diâmetro máximo do saco aneurismático: XXX cm
Extensão do aneurisma: XXX cm
Extensão da aorta infrarrenal: XXX cm
/// FIM DAS MEDIDAS CASO TENHA ANEURISMA DE AAAIR
Bifurcação aórtica: XXX cm
Artéria ilíaca comum direita pré bifurcação: XXXXX cm de diâmetro e XXX cm de extensão
Artéria ilíaca externa direita : XXX cm
Artéria ilíaca comum esquerda pré bifurcação: XXX cm de diâmetro e XXX cm de extensão
Artéria ilíaca externa esquerda : XXX cm',
    '{"indicacao": "", "tecnica": "Exame realizado com aquisição multislice, antes e após a injeção intravenosa do meio de contraste iodado.", "achados": "Ramos supra-aórticos com trajeto e calibre preservados.\\nAorta torácica e abdominal de contornos regulares, com trajeto e calibre preservados. Não há evidências de aneurismas ou dissecções.\\nTronco celíaco, artérias mesentéricas superior e inferior pérvias e com calibre preservado.\\nArtérias renais únicas, pérvias e com calibre preservado.\\nArtérias ilíacas comuns, externas e internas, femorais comuns e segmento proximal das femorais superficiais e profundas pérvios, sem alterações significativas de calibre.\\nAteromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.\\n*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.\\n*** Aneurisma fusiforme da aorta abdominal que se estende inferiormente até o plano da bifurcação aórtica. Emergem do aneurisma as artérias XXXXX, que se apresentam pérvias e com calibre habitual. Medidas relevantes descritas abaixo.\\n*** Aneurisma fusiforme da aorta torácica, que se inicia num plano cerca de .... cm e se estende por cerca de ..... cm. O calibre máximo do aneurisma é de ...... cm. O calibre da aorta superiormente à dilatação é de .... cm e distalmente de ..... cm.\\nNota-se ainda, trombose mural na ao longo da luz do segmento dilatado.\\n*** Controle pós-operatório de correção endovascular de aneurisma da aorta torácica, com colocação de endoprótese metálica. Endoprótese com posicionamento habitual e sem sinais de extravasamentos do meio de contraste. O diâmetro máximo do saco aneurismático é de ...........cm.\\n*** Controle pós-operatório de correção de aneurisma da aorta  torácica com colocação de prótese, sem sinais de extravasamentos. Não são identificadas coleções no leito cirúrgico.\\nDiâmetros máximos da aorta:\\nSeio de Valsalva: XXX cm\\nAorta ascendente: XXX cm\\nCrossa da aorta: XXX cm\\nAorta descendente: XXX cm\\nTransição toracoabdominal: XXX cm\\nAorta abdominal suprarrenal: XXX cm\\nAorta abdominal infrarrenal: XXX cm\\n/// INÍCIO DAS MEDIDAS CASO TENHA ANEURISMA AAAIR (INCLUIR AS ABAIXO para ancoragem do stent)\\nAltura da emergência das artérias renais: XXX cm\\nAspecto cranial do segmento infra-renal: XXX cm\\nDiâmetro máximo do saco aneurismático: XXX cm\\nExtensão do aneurisma: XXX cm\\nExtensão da aorta infrarrenal: XXX cm\\n/// FIM DAS MEDIDAS CASO TENHA ANEURISMA DE AAAIR\\nBifurcação aórtica: XXX cm\\nArtéria ilíaca comum direita pré bifurcação: XXXXX cm de diâmetro e XXX cm de extensão\\nArtéria ilíaca externa direita : XXX cm\\nArtéria ilíaca comum esquerda pré bifurcação: XXX cm de diâmetro e XXX cm de extensão\\nArtéria ilíaca externa esquerda : XXX cm", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada da Aorta Torácica e Abdominal'
    AND ativo = 1
);

-- [007] Angiotomografia Computadorizada da Aorta Torácica, Abdominal e das Artérias dos 
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada da Aorta Torácica, Abdominal e das Artérias dos Membros Inferiores',
    'TC',
    '######### Sinais de ateromatose difusa, caracterizados por espessamento e calcificações parietais esparsas pelo leito estudado.
######### Associam-se irregularidades da superfície luminal, indicativas de ulcerações.
Segmento proximal do tronco braquiocefálico, das artérias subclávias e da artéria carótida comum esquerda pérvios, sem estenoses.
######### Como variante da normalidade o tronco braquiocefálico e a artéria carótida comum esquerda tem origem conjunta no arco aórtico.
Aorta torácica pérvia, com trajeto e calibre preservados.
Aorta abdominal pérvia, com trajeto e calibre preservados.
Tronco celíaco, artérias mesentéricas superior e inferior pérvios e com calibre preservado.
Artérias renais únicas, pérvias e com calibre preservado.
Artérias ilíacas comuns, externas e internas pérvias, sem alterações significativas de calibre.
######### Aneurisma fusiforme da aorta abdominal infrarrenal, que se inicia num plano cerca de [<>] cm inferiormente à emergência das artérias renais e se estende por cerca de [<>] cm. O calibre máximo do aneurisma é de [<>] cm. O calibre da aorta superiormente à dilatação é de [<>] cm e distalmente de [<>] cm. Nota-se ainda trombose mural ao longo da luz do segmento dilatado.
########## Controle pós-operatório de correção endovascular de aneurisma da aorta abdominal infrarrenal, com colocação de endoprótese metálica aortobi-ilíaca. Endoprótese com posicionamento habitual e sem sinais de extravasamentos do meio de contraste. O diâmetro máximo do saco aneurismático é de [<>] cm.
Medidas da aorta:
Plano valvar = [<>] cm
Seio coronariano = [<>] cm
Junção sinotubular = [<>] cm
Segmento tubular = [<>] cm
Joelho anterior da crossa = cm
Joelho posterior da crossa = [<>] cm
Aorta descendente = [<>] cm
Transição toracoabdominal = [<>] cm
Segmento abdominal suprarrenal = [<>] cm
Segmento abdominal infrarrenal = [<>] cm
#############Caso haja aneurisma não corrigido#############
Diâmetro máximo do aneurisma = [<>] cm
Colo proximal = [<>] cm de calibre e [<>] cm de extensão
Colo distal = [<>] cm de calibre e [<>] cm de extensão
Comprimento do segmento aórtico infrarrenal = [<>] cm
Artéria ilíaca comum direita = [<>] cm de calibre e [<>] cm de extensão
Artéria ilíaca comum esquerda = [<>] cm de calibre e [<>] cm de extensão
Membro inferior direito:
Artérias femorais comum, superficial e profunda pérvias, com calibre preservado.
Artéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa pérvios, sem alterações significativas de calibre.
Membro inferior esquerdo:
Artérias femorais comum, superficial e profunda pérvias, com calibre preservado.
Artéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa pérvios, sem alterações significativas de calibre.
- [<>]',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "######### Sinais de ateromatose difusa, caracterizados por espessamento e calcificações parietais esparsas pelo leito estudado.\\n######### Associam-se irregularidades da superfície luminal, indicativas de ulcerações.\\nSegmento proximal do tronco braquiocefálico, das artérias subclávias e da artéria carótida comum esquerda pérvios, sem estenoses.\\n######### Como variante da normalidade o tronco braquiocefálico e a artéria carótida comum esquerda tem origem conjunta no arco aórtico.\\nAorta torácica pérvia, com trajeto e calibre preservados.\\nAorta abdominal pérvia, com trajeto e calibre preservados.\\nTronco celíaco, artérias mesentéricas superior e inferior pérvios e com calibre preservado.\\nArtérias renais únicas, pérvias e com calibre preservado.\\nArtérias ilíacas comuns, externas e internas pérvias, sem alterações significativas de calibre.\\n######### Aneurisma fusiforme da aorta abdominal infrarrenal, que se inicia num plano cerca de [<>] cm inferiormente à emergência das artérias renais e se estende por cerca de [<>] cm. O calibre máximo do aneurisma é de [<>] cm. O calibre da aorta superiormente à dilatação é de [<>] cm e distalmente de [<>] cm. Nota-se ainda trombose mural ao longo da luz do segmento dilatado.\\n########## Controle pós-operatório de correção endovascular de aneurisma da aorta abdominal infrarrenal, com colocação de endoprótese metálica aortobi-ilíaca. Endoprótese com posicionamento habitual e sem sinais de extravasamentos do meio de contraste. O diâmetro máximo do saco aneurismático é de [<>] cm.\\nMedidas da aorta:\\nPlano valvar = [<>] cm\\nSeio coronariano = [<>] cm\\nJunção sinotubular = [<>] cm\\nSegmento tubular = [<>] cm\\nJoelho anterior da crossa = cm\\nJoelho posterior da crossa = [<>] cm\\nAorta descendente = [<>] cm\\nTransição toracoabdominal = [<>] cm\\nSegmento abdominal suprarrenal = [<>] cm\\nSegmento abdominal infrarrenal = [<>] cm\\n#############Caso haja aneurisma não corrigido#############\\nDiâmetro máximo do aneurisma = [<>] cm\\nColo proximal = [<>] cm de calibre e [<>] cm de extensão\\nColo distal = [<>] cm de calibre e [<>] cm de extensão\\nComprimento do segmento aórtico infrarrenal = [<>] cm\\nArtéria ilíaca comum direita = [<>] cm de calibre e [<>] cm de extensão\\nArtéria ilíaca comum esquerda = [<>] cm de calibre e [<>] cm de extensão\\nMembro inferior direito:\\nArtérias femorais comum, superficial e profunda pérvias, com calibre preservado.\\nArtéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa pérvios, sem alterações significativas de calibre.\\nMembro inferior esquerdo:\\nArtérias femorais comum, superficial e profunda pérvias, com calibre preservado.\\nArtéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa pérvios, sem alterações significativas de calibre.\\n- [<>]", "impressao": "Exame sem alterações significativas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada da Aorta Torácica, Abdominal e das Artérias dos Membros Inferiores'
    AND ativo = 1
);

-- [008] Angiotomografia Computadorizada da Aorta Torácica e Abdominal e Artérias Ilíacas
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada da Aorta Torácica e Abdominal e Artérias Ilíacas',
    'TC',
    'Ramos supra-aórticos com trajeto e calibre preservados.
Aorta torácica e abdominal de contornos regulares, com trajeto e calibre preservados. Não há evidências de aneurismas ou dissecções.
Tronco celíaco, artérias mesentéricas superior e inferior pérvias e com calibre preservado.
Artérias renais únicas, pérvias e com calibre preservado.
Artérias ilíacas comuns, externas e internas, femorais comuns e segmento proximal das femorais superficiais e profundas pérvios, sem alterações significativas de calibre.
Ateromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.
*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.
*** Aneurisma fusiforme da aorta abdominal que se estende inferiormente até o plano da bifurcação aórtica. Emergem do aneurisma as artérias XXXXX, que se apresentam pérvias e com calibre habitual. Medidas relevantes descritas abaixo.
*** Aneurisma fusiforme da aorta torácica, que se inicia num plano cerca de .... cm e se estende por cerca de ..... cm. O calibre máximo do aneurisma é de ...... cm. O calibre da aorta superiormente à dilatação é de .... cm e distalmente de ..... cm.
Nota-se ainda, trombose mural na ao longo da luz do segmento dilatado.
*** Controle pós-operatório de correção endovascular de aneurisma da aorta torácica, com colocação de endoprótese metálica. Endoprótese com posicionamento habitual e sem sinais de extravasamentos do meio de contraste. O diâmetro máximo do saco aneurismático é de ...........cm.
*** Controle pós-operatório de correção de aneurisma da aorta  torácica com colocação de prótese, sem sinais de extravasamentos. Não são identificadas coleções no leito cirúrgico.
Diâmetros máximos da aorta:
Seio de Valsalva: XXX cm
Aorta ascendente: XXX cm
Crossa da aorta: XXX cm
Aorta descendente: XXX cm
Transição toracoabdominal: XXX cm
Aorta abdominal suprarrenal: XXX cm
Aorta abdominal infrarrenal: XXX cm
/// INÍCIO DAS MEDIDAS CASO TENHA ANEURISMA AAAIR (INCLUIR AS ABAIXO para ancoragem do stent)
Altura da emergência das artérias renais: XXX cm
Aspecto cranial do segmento infra-renal: XXX cm
Diâmetro máximo do saco aneurismático: XXX cm
Extensão do aneurisma: XXX cm
Extensão da aorta infrarrenal: XXX cm
/// FIM DAS MEDIDAS CASO TENHA ANEURISMA DE AAAIR
Bifurcação aórtica: XXX cm
Artéria ilíaca comum direita pré bifurcação: XXXXX cm de diâmetro e XXX cm de extensão
Artéria ilíaca externa direita : XXX cm
Artéria ilíaca comum esquerda pré bifurcação: XXX cm de diâmetro e XXX cm de extensão
Artéria ilíaca externa esquerda : XXX cm',
    '{"indicacao": "", "tecnica": "Exame realizado com aquisição multislice, antes e após a injeção intravenosa do meio de contraste iodado.", "achados": "Ramos supra-aórticos com trajeto e calibre preservados.\\nAorta torácica e abdominal de contornos regulares, com trajeto e calibre preservados. Não há evidências de aneurismas ou dissecções.\\nTronco celíaco, artérias mesentéricas superior e inferior pérvias e com calibre preservado.\\nArtérias renais únicas, pérvias e com calibre preservado.\\nArtérias ilíacas comuns, externas e internas, femorais comuns e segmento proximal das femorais superficiais e profundas pérvios, sem alterações significativas de calibre.\\nAteromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.\\n*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.\\n*** Aneurisma fusiforme da aorta abdominal que se estende inferiormente até o plano da bifurcação aórtica. Emergem do aneurisma as artérias XXXXX, que se apresentam pérvias e com calibre habitual. Medidas relevantes descritas abaixo.\\n*** Aneurisma fusiforme da aorta torácica, que se inicia num plano cerca de .... cm e se estende por cerca de ..... cm. O calibre máximo do aneurisma é de ...... cm. O calibre da aorta superiormente à dilatação é de .... cm e distalmente de ..... cm.\\nNota-se ainda, trombose mural na ao longo da luz do segmento dilatado.\\n*** Controle pós-operatório de correção endovascular de aneurisma da aorta torácica, com colocação de endoprótese metálica. Endoprótese com posicionamento habitual e sem sinais de extravasamentos do meio de contraste. O diâmetro máximo do saco aneurismático é de ...........cm.\\n*** Controle pós-operatório de correção de aneurisma da aorta  torácica com colocação de prótese, sem sinais de extravasamentos. Não são identificadas coleções no leito cirúrgico.\\nDiâmetros máximos da aorta:\\nSeio de Valsalva: XXX cm\\nAorta ascendente: XXX cm\\nCrossa da aorta: XXX cm\\nAorta descendente: XXX cm\\nTransição toracoabdominal: XXX cm\\nAorta abdominal suprarrenal: XXX cm\\nAorta abdominal infrarrenal: XXX cm\\n/// INÍCIO DAS MEDIDAS CASO TENHA ANEURISMA AAAIR (INCLUIR AS ABAIXO para ancoragem do stent)\\nAltura da emergência das artérias renais: XXX cm\\nAspecto cranial do segmento infra-renal: XXX cm\\nDiâmetro máximo do saco aneurismático: XXX cm\\nExtensão do aneurisma: XXX cm\\nExtensão da aorta infrarrenal: XXX cm\\n/// FIM DAS MEDIDAS CASO TENHA ANEURISMA DE AAAIR\\nBifurcação aórtica: XXX cm\\nArtéria ilíaca comum direita pré bifurcação: XXXXX cm de diâmetro e XXX cm de extensão\\nArtéria ilíaca externa direita : XXX cm\\nArtéria ilíaca comum esquerda pré bifurcação: XXX cm de diâmetro e XXX cm de extensão\\nArtéria ilíaca externa esquerda : XXX cm", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada da Aorta Torácica e Abdominal e Artérias Ilíacas'
    AND ativo = 1
);

-- [009] Angiotomografia da Aorta Torácica e Arterial dos Membros Superiores
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia da Aorta Torácica e Arterial dos Membros Superiores',
    'TC',
    'Tronco arterial pulmonar e artérias pulmonares principais pérvios e com calibre normal.
Segmento proximal dos troncos supra-aórticos com trajeto e calibre conservados.
Aorta torácica pérvia, com trajeto conservado, sem alterações significativas de calibre.
Medidas da aorta torácica:
Plano valvar = [<>] cm
Seio coronariano = [<>] cm
Junção sinotubular = [<>] cm
Segmento tubular = [<>] cm
Joelho anterior da crossa = [<>] cm
Joelho posterior da crossa = [<>] cm
Aorta descendente = [<>] cm
Ausência de linfonodomegalias mediastinais.
Parênquima pulmonar com sinal preservado, salientando-se a baixa eficácia do método para avaliação dos pulmões.
Ausência de derrame pleural significativo.
Membro superior DIREITO:
Artérias subclávia, axilar, braquial, radial e ulnar pérvias, com calibre preservado.
Membro superior ESQUERDO:
Artérias subclávia, axilar, braquial, radial e ulnar pérvias, com calibre preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Tronco arterial pulmonar e artérias pulmonares principais pérvios e com calibre normal.\\nSegmento proximal dos troncos supra-aórticos com trajeto e calibre conservados.\\nAorta torácica pérvia, com trajeto conservado, sem alterações significativas de calibre.\\nMedidas da aorta torácica:\\nPlano valvar = [<>] cm\\nSeio coronariano = [<>] cm\\nJunção sinotubular = [<>] cm\\nSegmento tubular = [<>] cm\\nJoelho anterior da crossa = [<>] cm\\nJoelho posterior da crossa = [<>] cm\\nAorta descendente = [<>] cm\\nAusência de linfonodomegalias mediastinais.\\nParênquima pulmonar com sinal preservado, salientando-se a baixa eficácia do método para avaliação dos pulmões.\\nAusência de derrame pleural significativo.\\nMembro superior DIREITO:\\nArtérias subclávia, axilar, braquial, radial e ulnar pérvias, com calibre preservado.\\nMembro superior ESQUERDO:\\nArtérias subclávia, axilar, braquial, radial e ulnar pérvias, com calibre preservado.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia da Aorta Torácica e Arterial dos Membros Superiores'
    AND ativo = 1
);

-- [010] Angiotomografia de Aorta Torácica e das Artérias do Membro Superior Direito (pes
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia de Aorta Torácica e das Artérias do Membro Superior Direito (pesquisa de Síndrome do Desfiladeiro Torácico)',
    'TC',
    'Tronco arterial braquiocefálico, artérias carótida comum, artérias subclávia e artéria axilar pérvias, com trajeto e calibre conservados.
######### Como variante da normalidade, o tronco braquiocefálico e a artéria carótida comum esquerda têm origem conjunta no arco aórtico.
Durante as manobras dinâmicas, não foram observadas reduções significativas dos calibres ou compressões extrínsecas sobre a artéria subclávia.
Veia jugular, veia subclávia e veia cava superior pérvias, sem alterações significativas de calibre ou tromboses.
Durante as manobras dinâmicas, não foram observadas reduções significativas do calibre da veia subclávia.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.\\nForam realizadas manobras para a pesquisa de síndrome do desfiladeiro torácico.", "achados": "Tronco arterial braquiocefálico, artérias carótida comum, artérias subclávia e artéria axilar pérvias, com trajeto e calibre conservados.\\n######### Como variante da normalidade, o tronco braquiocefálico e a artéria carótida comum esquerda têm origem conjunta no arco aórtico.\\nDurante as manobras dinâmicas, não foram observadas reduções significativas dos calibres ou compressões extrínsecas sobre a artéria subclávia.\\nVeia jugular, veia subclávia e veia cava superior pérvias, sem alterações significativas de calibre ou tromboses.\\nDurante as manobras dinâmicas, não foram observadas reduções significativas do calibre da veia subclávia.", "impressao": "Exame sem alterações significativas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia de Aorta Torácica e das Artérias do Membro Superior Direito (pesquisa de Síndrome do Desfiladeiro Torácico)'
    AND ativo = 1
);

-- [011] Angiotomografia de Aorta Torácica e das Artérias do
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia de Aorta Torácica e das Artérias do',
    'TC',
    '',
    '{"indicacao": "", "tecnica": "", "achados": "", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia de Aorta Torácica e das Artérias do'
    AND ativo = 1
);

-- [012] (pesquisa de Síndrome do Desfiladeiro Torácico)
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    '(pesquisa de Síndrome do Desfiladeiro Torácico)',
    'TC',
    'Tronco arterial braquiocefálico, artéria carótida comum, artéria subclávia e artéria axilar pérvias, com trajeto e calibre conservados.
######### Como variante da normalidade, o tronco braquiocefálico e a artéria carótida comum esquerda têm origem conjunta no arco aórtico.
Durante as manobras dinâmicas, não foram observadas reduções significativas dos calibres ou compressões extrínsecas sobre a artéria subclávia.
Veia jugular, veia subclávia e veia cava superior pérvias, sem alterações significativas de calibre ou tromboses.
Durante as manobras dinâmicas, não foram observadas reduções significativas do calibre da veia subclávia.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.\\nForam realizadas manobras para a pesquisa de síndrome do desfiladeiro torácico.", "achados": "Tronco arterial braquiocefálico, artéria carótida comum, artéria subclávia e artéria axilar pérvias, com trajeto e calibre conservados.\\n######### Como variante da normalidade, o tronco braquiocefálico e a artéria carótida comum esquerda têm origem conjunta no arco aórtico.\\nDurante as manobras dinâmicas, não foram observadas reduções significativas dos calibres ou compressões extrínsecas sobre a artéria subclávia.\\nVeia jugular, veia subclávia e veia cava superior pérvias, sem alterações significativas de calibre ou tromboses.\\nDurante as manobras dinâmicas, não foram observadas reduções significativas do calibre da veia subclávia.", "impressao": "Exame sem alterações significativas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = '(pesquisa de Síndrome do Desfiladeiro Torácico)'
    AND ativo = 1
);

-- [013] Angiotomografia da Aorta Torácica e Abdominal, e Arterial e Venosa dos Membros I
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia da Aorta Torácica e Abdominal, e Arterial e Venosa dos Membros Inferiores',
    'TC',
    '######### Sinais de ateromatose difusa, caracterizados por espessamento e calcificações parietais esparsas pelo leito estudado.
######### Associam-se irregularidades da superfície luminal, indicativas de ulcerações.
Segmento proximal do tronco braquiocefálico, das artérias subclávias e da artéria carótida comum esquerda pérvios, sem estenoses.
######### Como variante da normalidade o tronco braquiocefálico e a artéria carótida comum esquerda tem origem conjunta no arco aórtico.
Aorta torácica pérvia, com trajeto e calibre preservados.
Aorta abdominal pérvia, com trajeto e calibre preservados.
Tronco celíaco, artérias mesentéricas superior e inferior pérvios e com calibre preservado.
Artérias renais únicas, pérvias e com calibre preservado.
Artérias ilíacas comuns, externas e internas pérvias, sem alterações significativas de calibre.
######### Aneurisma fusiforme da aorta abdominal infrarrenal, que se inicia num plano cerca de [<>] cm inferiormente à emergência das artérias renais e se estende por cerca de [<>] cm. O calibre máximo do aneurisma é de [<>] cm. O calibre da aorta superiormente à dilatação é de [<>] cm e distalmente de [<>] cm. Nota-se ainda trombose mural ao longo da luz do segmento dilatado.
########## Controle pós-operatório de correção endovascular de aneurisma da aorta abdominal infrarrenal, com colocação de endoprótese metálica aortoilíaca bilateral. Endoprótese com posicionamento habitual e sem sinais de extravasamentos do meio de contraste. O diâmetro máximo do saco aneurismático é de [<>] cm.
Medidas da aorta:
Plano valvar = [<>] cm
Seio coronariano = [<>] cm
Junção sinotubular = [<>] cm
Segmento tubular = [<>] cm
Joelho anterior da crossa = cm
Joelho posterior da crossa = [<>] cm
Aorta descendente = [<>] cm
Transição toracoabdominal = [<>] cm
Segmento abdominal suprarrenal = [<>] cm
Segmento abdominal infrarrenal = [<>] cm
#############Caso haja aneurisma não corrigido#############
Diâmetro máximo do aneurisma = [<>] cm
Colo proximal = [<>] cm de calibre e [<>] cm de extensão
Colo distal = [<>] cm de calibre e [<>] cm de extensão
Comprimento do segmento aórtico infrarrenal = [<>] cm
Artéria ilíaca comum direita = [<>] cm de calibre e [<>] cm de extensão
Artéria ilíaca comum esquerda = [<>] cm de calibre e [<>] cm de extensão
Membro inferior DIREITO:
Angio ARTERIAL:
Artérias femorais comum, superficial e profunda pérvias, com calibre preservado.
Artéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior pérvias, sem alterações significativas de calibre.
Angio VENOSA:
Veias ilíacas externa e interna, femorais comuns e profundas, femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.
Membro inferior ESQUERDO:
Angio ARTERIAL:
Artérias femorais comum, superficial e profunda pérvias, com calibre preservado.
Artéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior pérvias, sem alterações significativas de calibre.
Angio VENOSA:
Veias ilíacas externa e interna, femorais comuns e profundas, femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "######### Sinais de ateromatose difusa, caracterizados por espessamento e calcificações parietais esparsas pelo leito estudado.\\n######### Associam-se irregularidades da superfície luminal, indicativas de ulcerações.\\nSegmento proximal do tronco braquiocefálico, das artérias subclávias e da artéria carótida comum esquerda pérvios, sem estenoses.\\n######### Como variante da normalidade o tronco braquiocefálico e a artéria carótida comum esquerda tem origem conjunta no arco aórtico.\\nAorta torácica pérvia, com trajeto e calibre preservados.\\nAorta abdominal pérvia, com trajeto e calibre preservados.\\nTronco celíaco, artérias mesentéricas superior e inferior pérvios e com calibre preservado.\\nArtérias renais únicas, pérvias e com calibre preservado.\\nArtérias ilíacas comuns, externas e internas pérvias, sem alterações significativas de calibre.\\n######### Aneurisma fusiforme da aorta abdominal infrarrenal, que se inicia num plano cerca de [<>] cm inferiormente à emergência das artérias renais e se estende por cerca de [<>] cm. O calibre máximo do aneurisma é de [<>] cm. O calibre da aorta superiormente à dilatação é de [<>] cm e distalmente de [<>] cm. Nota-se ainda trombose mural ao longo da luz do segmento dilatado.\\n########## Controle pós-operatório de correção endovascular de aneurisma da aorta abdominal infrarrenal, com colocação de endoprótese metálica aortoilíaca bilateral. Endoprótese com posicionamento habitual e sem sinais de extravasamentos do meio de contraste. O diâmetro máximo do saco aneurismático é de [<>] cm.\\nMedidas da aorta:\\nPlano valvar = [<>] cm\\nSeio coronariano = [<>] cm\\nJunção sinotubular = [<>] cm\\nSegmento tubular = [<>] cm\\nJoelho anterior da crossa = cm\\nJoelho posterior da crossa = [<>] cm\\nAorta descendente = [<>] cm\\nTransição toracoabdominal = [<>] cm\\nSegmento abdominal suprarrenal = [<>] cm\\nSegmento abdominal infrarrenal = [<>] cm\\n#############Caso haja aneurisma não corrigido#############\\nDiâmetro máximo do aneurisma = [<>] cm\\nColo proximal = [<>] cm de calibre e [<>] cm de extensão\\nColo distal = [<>] cm de calibre e [<>] cm de extensão\\nComprimento do segmento aórtico infrarrenal = [<>] cm\\nArtéria ilíaca comum direita = [<>] cm de calibre e [<>] cm de extensão\\nArtéria ilíaca comum esquerda = [<>] cm de calibre e [<>] cm de extensão\\nMembro inferior DIREITO:\\nAngio ARTERIAL:\\nArtérias femorais comum, superficial e profunda pérvias, com calibre preservado.\\nArtéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior pérvias, sem alterações significativas de calibre.\\nAngio VENOSA:\\nVeias ilíacas externa e interna, femorais comuns e profundas, femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.\\nMembro inferior ESQUERDO:\\nAngio ARTERIAL:\\nArtérias femorais comum, superficial e profunda pérvias, com calibre preservado.\\nArtéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior pérvias, sem alterações significativas de calibre.\\nAngio VENOSA:\\nVeias ilíacas externa e interna, femorais comuns e profundas, femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.", "impressao": "Exame sem alterações significativas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia da Aorta Torácica e Abdominal, e Arterial e Venosa dos Membros Inferiores'
    AND ativo = 1
);

-- [014] Angiotomografia de Aorta Torácica e das Artérias do Membro Superior Esquerdo (pe
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia de Aorta Torácica e das Artérias do Membro Superior Esquerdo (pesquisa de Síndrome do Desfiladeiro Torácico)',
    'TC',
    'Tronco arterial braquiocefálico, artéria carótida comum, artéria subclávia e artéria axilar pérvias, com trajeto e calibre conservados.
######### Como variante da normalidade, o tronco braquiocefálico e a artéria carótida comum esquerda têm origem conjunta no arco aórtico.
Durante as manobras dinâmicas, não foram observadas reduções significativas dos calibres ou compressões extrínsecas sobre a artéria subclávia.
Veia jugular, veia subclávia e veia cava superior pérvias, sem alterações significativas de calibre ou tromboses.
Durante as manobras dinâmicas, não foram observadas reduções significativas do calibre da veia subclávia.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.\\nForam realizadas manobras para a pesquisa de síndrome do desfiladeiro torácico.", "achados": "Tronco arterial braquiocefálico, artéria carótida comum, artéria subclávia e artéria axilar pérvias, com trajeto e calibre conservados.\\n######### Como variante da normalidade, o tronco braquiocefálico e a artéria carótida comum esquerda têm origem conjunta no arco aórtico.\\nDurante as manobras dinâmicas, não foram observadas reduções significativas dos calibres ou compressões extrínsecas sobre a artéria subclávia.\\nVeia jugular, veia subclávia e veia cava superior pérvias, sem alterações significativas de calibre ou tromboses.\\nDurante as manobras dinâmicas, não foram observadas reduções significativas do calibre da veia subclávia.", "impressao": "Exame sem alterações significativas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia de Aorta Torácica e das Artérias do Membro Superior Esquerdo (pesquisa de Síndrome do Desfiladeiro Torácico)'
    AND ativo = 1
);

-- [015] Angiotomografia Computadorizada do Abdome Superior
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada do Abdome Superior',
    'TC',
    'Aorta abdominal pérvia, com trajeto e calibre preservados.
Tronco celíaco, artérias mesentéricas superior e inferior pérvias e com calibre preservado.
Artérias renais únicas, pérvias e com calibre preservado.
Ateromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.
*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.
*** Aneurisma fusiforme da aorta abdominal que se estende inferiormente até o plano da bifurcação aórtica. Emergem do aneurisma as artérias XXXXX, que se apresentam pérvias e com calibre habitual. Medidas relevantes descritas abaixo.
*** Nota-se ainda, trombose mural na ao longo da luz do segmento dilatado.
Diâmetros máximos da aorta:
- Transição toracoabdominal: XXX cm
- Aorta abdominal suprarrenal: XXX cm
- Aorta abdominal infrarrenal: XXX cm',
    '{"indicacao": "", "tecnica": "Exame realizado com aquisição multislice, com a injeção intravenosa do meio de contraste iodado.", "achados": "Aorta abdominal pérvia, com trajeto e calibre preservados.\\nTronco celíaco, artérias mesentéricas superior e inferior pérvias e com calibre preservado.\\nArtérias renais únicas, pérvias e com calibre preservado.\\nAteromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.\\n*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.\\n*** Aneurisma fusiforme da aorta abdominal que se estende inferiormente até o plano da bifurcação aórtica. Emergem do aneurisma as artérias XXXXX, que se apresentam pérvias e com calibre habitual. Medidas relevantes descritas abaixo.\\n*** Nota-se ainda, trombose mural na ao longo da luz do segmento dilatado.\\nDiâmetros máximos da aorta:\\n- Transição toracoabdominal: XXX cm\\n- Aorta abdominal suprarrenal: XXX cm\\n- Aorta abdominal infrarrenal: XXX cm", "impressao": "Exame dentro dos padrões da normalidade.\\n]", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada do Abdome Superior'
    AND ativo = 1
);

-- [016] Angiotomografia Computadorizada do Abdome Total
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada do Abdome Total',
    'TC',
    'Aorta abdominal pérvia, com trajeto e calibre preservados.
Tronco celíaco, artérias mesentéricas superior e inferior pérvias e com calibre preservado.
Artérias renais únicas, pérvias e com calibre preservado.
Artérias ilíacas comuns, externas e internas, femorais comuns e segmento proximal das femorais superficiais e profundas pérvios, sem alterações significativas de calibre.
Ateromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.
*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.
*** Aneurisma fusiforme da aorta abdominal que se estende inferiormente até o plano da bifurcação aórtica. Emergem do aneurisma as artérias XXXXX, que se apresentam pérvias e com calibre habitual. Medidas relevantes descritas abaixo.
*** Nota-se ainda, trombose mural na ao longo da luz do segmento dilatado.
*** Controle pós-operatório de correção de aneurisma da aorta abdominal com colocação de prótese, sem sinais de extravasamentos. Não são identificadas coleções no leito cirúrgico.
Diâmetros máximos da aorta:
- Transição toracoabdominal: XXX cm
- Aorta abdominal suprarrenal: XXX cm
- Aorta abdominal infrarrenal: XXX cm
/// INICIO DAS MEDIDAS CASO TENHA ANEURISMA AAAIR (INCLUIR AS ABAIXO para ancoragem do stent)
- Altura da emergência das artérias renais: XXX cm
- Aspecto cranial do segmento infra-renal: XXX cm
- Diâmetro máximo do saco aneurismático: XXX cm
- Extensão do aneurisma: XXX cm
- Extensão da aorta infra-renal: CASO_TENHA_ANEURISMA////
/// FIM DAS MEDIDAS CASO TENHA ANEURISMA DE AAAIR
- Bifurcação aórtica: XXX cm
- Artéria ilíaca comum direita pré bifurcação: XXXXX cm de diâmetro e XXX cm de extensão
- Artéria ilíaca externa direita : XXX cm
- Artéria ilíaca comum esquerda pré bifurcação: XXX cm de diâmetro e XXX cm de extensão
- Artéria ilíaca externa esquerda : XXX cm',
    '{"indicacao": "", "tecnica": "Exame realizado com aquisição multislice, antes e após a injeção intravenosa do meio de contraste iodado.", "achados": "Aorta abdominal pérvia, com trajeto e calibre preservados.\\nTronco celíaco, artérias mesentéricas superior e inferior pérvias e com calibre preservado.\\nArtérias renais únicas, pérvias e com calibre preservado.\\nArtérias ilíacas comuns, externas e internas, femorais comuns e segmento proximal das femorais superficiais e profundas pérvios, sem alterações significativas de calibre.\\nAteromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.\\n*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.\\n*** Aneurisma fusiforme da aorta abdominal que se estende inferiormente até o plano da bifurcação aórtica. Emergem do aneurisma as artérias XXXXX, que se apresentam pérvias e com calibre habitual. Medidas relevantes descritas abaixo.\\n*** Nota-se ainda, trombose mural na ao longo da luz do segmento dilatado.\\n*** Controle pós-operatório de correção de aneurisma da aorta abdominal com colocação de prótese, sem sinais de extravasamentos. Não são identificadas coleções no leito cirúrgico.\\nDiâmetros máximos da aorta:\\n- Transição toracoabdominal: XXX cm\\n- Aorta abdominal suprarrenal: XXX cm\\n- Aorta abdominal infrarrenal: XXX cm\\n/// INICIO DAS MEDIDAS CASO TENHA ANEURISMA AAAIR (INCLUIR AS ABAIXO para ancoragem do stent)\\n- Altura da emergência das artérias renais: XXX cm\\n- Aspecto cranial do segmento infra-renal: XXX cm\\n- Diâmetro máximo do saco aneurismático: XXX cm\\n- Extensão do aneurisma: XXX cm\\n- Extensão da aorta infra-renal: CASO_TENHA_ANEURISMA////\\n/// FIM DAS MEDIDAS CASO TENHA ANEURISMA DE AAAIR\\n- Bifurcação aórtica: XXX cm\\n- Artéria ilíaca comum direita pré bifurcação: XXXXX cm de diâmetro e XXX cm de extensão\\n- Artéria ilíaca externa direita : XXX cm\\n- Artéria ilíaca comum esquerda pré bifurcação: XXX cm de diâmetro e XXX cm de extensão\\n- Artéria ilíaca externa esquerda : XXX cm", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada do Abdome Total'
    AND ativo = 1
);

-- [017] Angiotomografia Arterial Cervical
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Arterial Cervical',
    'TC',
    'Arco aórtico, tronco braquiocefálico e porções proximais das artérias subclávias com trajeto e calibre preservados.
Origem habitual dos vasos no arco aórtico.
Artérias carótidas comuns, segmentos cervicais das carótidas internas e externas de calibre e contornos normais.
Segmentos cervicais das artérias vertebrais de trajeto, calibre e contornos normais.
Ausência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Arco aórtico, tronco braquiocefálico e porções proximais das artérias subclávias com trajeto e calibre preservados.\\nOrigem habitual dos vasos no arco aórtico.\\nArtérias carótidas comuns, segmentos cervicais das carótidas internas e externas de calibre e contornos normais.\\nSegmentos cervicais das artérias vertebrais de trajeto, calibre e contornos normais.\\nAusência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.", "impressao": "Exame dentro dos padrões da normalidade.\\nNão há evidências de estenoses ou aneurismas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Arterial Cervical'
    AND ativo = 1
);

-- [018] Angiotomografia Arterial Intracraniana e Cervical
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Arterial Intracraniana e Cervical',
    'TC',
    'Angio Arterial Intracraniana:
Segmentos intracranianos das artérias carótidas internas, artérias cerebrais anteriores, médias e posteriores de calibre, trajeto e contornos preservados.
Segmento intracraniano das artérias vertebrais e artéria basilar de calibre, trajeto e contornos preservados
Demais artérias intracranianas detectadas por esta técnica apresentando calibre, trajeto e contornos preservados.
Ausência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.
Angio Arterial Cervical:
Arco aórtico, tronco braquiocefálico e porções proximais das artérias subclávias com trajeto e calibre preservados.
Origem habitual dos vasos no arco aórtico.
Artérias carótidas comuns, segmentos cervicais das carótidas internas e externas de calibre e contornos normais.
Segmentos cervicais das artérias vertebrais de trajeto, calibre e contornos normais.
Ausência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Angio Arterial Intracraniana:\\nSegmentos intracranianos das artérias carótidas internas, artérias cerebrais anteriores, médias e posteriores de calibre, trajeto e contornos preservados.\\nSegmento intracraniano das artérias vertebrais e artéria basilar de calibre, trajeto e contornos preservados\\nDemais artérias intracranianas detectadas por esta técnica apresentando calibre, trajeto e contornos preservados.\\nAusência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.\\nAngio Arterial Cervical:\\nArco aórtico, tronco braquiocefálico e porções proximais das artérias subclávias com trajeto e calibre preservados.\\nOrigem habitual dos vasos no arco aórtico.\\nArtérias carótidas comuns, segmentos cervicais das carótidas internas e externas de calibre e contornos normais.\\nSegmentos cervicais das artérias vertebrais de trajeto, calibre e contornos normais.\\nAusência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.", "impressao": "Exame dentro dos padrões da normalidade.\\nNão há evidências de estenoses ou aneurismas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Arterial Intracraniana e Cervical'
    AND ativo = 1
);

-- [019] Angiotomografia Arterial do Tórax
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Arterial do Tórax',
    'TC',
    'Tronco arterial pulmonar e artérias pulmonares principais pérvios e com calibre normal.
Aorta torácica de contornos regulares, com trajeto e calibre preservados. Não há evidências de aneurismas ou dissecções.
Tronco arterial braquiocefálico, artérias subclávias e porções proximais das artérias carótidas comuns pérvias, com trajeto e calibre conservados.
⚠⚠⚠ Como variante da normalidade, o tronco braquiocefálico e a artéria carótida comum esquerda têm origem conjunta no arco aórtico.
⚠⚠⚠ Durante as manobras dinâmicas, não foram observadas reduções significativas de calibre ou compressões extrínsecas significativas sobre as artérias e veias subclávias.
⚠⚠⚠ Sinais de ateromatose difusa, caracterizados por espessamento mediointimal e placas calcificadas esparsas.
Demais achados:
Ausência de linfonodomegalias mediastinais ou hilares.
Demais estruturas do mediastino de aspecto preservado.
Traqueia e brônquios pérvios e com calibre preservado.
Parênquima pulmonar com atenuação habitual.
Espaços pleurais livres.
Estruturas ósseas sem alterações relevantes.
Imagens da transição toracoabdominal sem particularidades.',
    '{"indicacao": "", "tecnica": "Imagens obtidas por tecnologia de múltiplos detectores, com a administração do meio de contraste endovenoso.", "achados": "Tronco arterial pulmonar e artérias pulmonares principais pérvios e com calibre normal.\\nAorta torácica de contornos regulares, com trajeto e calibre preservados. Não há evidências de aneurismas ou dissecções.\\nTronco arterial braquiocefálico, artérias subclávias e porções proximais das artérias carótidas comuns pérvias, com trajeto e calibre conservados.\\n⚠⚠⚠ Como variante da normalidade, o tronco braquiocefálico e a artéria carótida comum esquerda têm origem conjunta no arco aórtico.\\n⚠⚠⚠ Durante as manobras dinâmicas, não foram observadas reduções significativas de calibre ou compressões extrínsecas significativas sobre as artérias e veias subclávias.\\n⚠⚠⚠ Sinais de ateromatose difusa, caracterizados por espessamento mediointimal e placas calcificadas esparsas.\\nDemais achados:\\nAusência de linfonodomegalias mediastinais ou hilares.\\nDemais estruturas do mediastino de aspecto preservado.\\nTraqueia e brônquios pérvios e com calibre preservado.\\nParênquima pulmonar com atenuação habitual.\\nEspaços pleurais livres.\\nEstruturas ósseas sem alterações relevantes.\\nImagens da transição toracoabdominal sem particularidades.", "impressao": "Exame sem alterações significativas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Arterial do Tórax'
    AND ativo = 1
);

-- [020] Angiotomografia Arterial e Venosa Cervical
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Arterial e Venosa Cervical',
    'TC',
    'Angio Arterial Cervical:
Arco aórtico, tronco braquiocefálico e porções proximais das artérias subclávias com trajeto e calibre preservados.
Origem habitual dos vasos no arco aórtico.
Artérias carótidas comuns, segmentos cervicais das carótidas internas e externas de calibre e contornos normais.
Segmentos cervicais das artérias vertebrais de trajeto, calibre e contornos normais.
Ausência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.
Angio Venosa Cervical:
Não foram observados sinais de circulação patológica.
Bulbos jugulares e veias jugulares internas pérvios.
Veias subclávias e segmentos incluídos no estudo (proximais) das veias axilares pérvios.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Angio Arterial Cervical:\\nArco aórtico, tronco braquiocefálico e porções proximais das artérias subclávias com trajeto e calibre preservados.\\nOrigem habitual dos vasos no arco aórtico.\\nArtérias carótidas comuns, segmentos cervicais das carótidas internas e externas de calibre e contornos normais.\\nSegmentos cervicais das artérias vertebrais de trajeto, calibre e contornos normais.\\nAusência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.\\nAngio Venosa Cervical:\\nNão foram observados sinais de circulação patológica.\\nBulbos jugulares e veias jugulares internas pérvios.\\nVeias subclávias e segmentos incluídos no estudo (proximais) das veias axilares pérvios.", "impressao": "Exame dentro dos padrões da normalidade. Não há evidências de estenoses ou aneurismas.\\nNão há evidências de trombose venosa recente.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Arterial e Venosa Cervical'
    AND ativo = 1
);

-- [021] Angiotomografia Arterial e Venosa, Cervical e Intracraniana
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Arterial e Venosa, Cervical e Intracraniana',
    'TC',
    'Arco aórtico, tronco braquiocefálico e porções proximais das artérias subclávias com trajeto e calibre preservados.
Origem habitual dos vasos no arco aórtico.
Artérias carótidas comuns, internas e externas e vertebrais com calibre e contornos normais.
Artérias cerebrais anteriores, médias e posteriores com calibre, trajeto e contornos preservados.
Demais artérias intracranianas detectadas por esta técnica apresentando calibre, trajeto e contornos preservados.
Ausência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.
Seio sagital superior, veia de Galeno, veias cerebrais internas, seio reto, seios transversos e sigmóides, e porção cranial das veias jugulares internas de calibre, trajeto e fluxo preservados.
Não foram observados sinais de circulação patológica.
XXX Assimetria do calibre e fluxo dos seios transversos, sendo menor à XX, mais comumente significativo de hipoplasia deste lado.
XXX Seio sagital superior, seio reto, veia cerebral magna e cerebrais internas com fluxo preservado.
XXX Imagens arredondadas junto ao seio XX com atenuação semelhante ao líquor em todas as sequências, compatíveis com granulações aracnoídeas.
Bulbos jugulares e veias jugulares internas pérvios.
Veias subclávias e segmentos incluídos no estudo (proximais) das veias axilares pérvios.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa de contraste iodado.", "achados": "Arco aórtico, tronco braquiocefálico e porções proximais das artérias subclávias com trajeto e calibre preservados.\\nOrigem habitual dos vasos no arco aórtico.\\nArtérias carótidas comuns, internas e externas e vertebrais com calibre e contornos normais.\\nArtérias cerebrais anteriores, médias e posteriores com calibre, trajeto e contornos preservados.\\nDemais artérias intracranianas detectadas por esta técnica apresentando calibre, trajeto e contornos preservados.\\nAusência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.\\nSeio sagital superior, veia de Galeno, veias cerebrais internas, seio reto, seios transversos e sigmóides, e porção cranial das veias jugulares internas de calibre, trajeto e fluxo preservados.\\nNão foram observados sinais de circulação patológica.\\nXXX Assimetria do calibre e fluxo dos seios transversos, sendo menor à XX, mais comumente significativo de hipoplasia deste lado.\\nXXX Seio sagital superior, seio reto, veia cerebral magna e cerebrais internas com fluxo preservado.\\nXXX Imagens arredondadas junto ao seio XX com atenuação semelhante ao líquor em todas as sequências, compatíveis com granulações aracnoídeas.\\nBulbos jugulares e veias jugulares internas pérvios.\\nVeias subclávias e segmentos incluídos no estudo (proximais) das veias axilares pérvios.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Arterial e Venosa, Cervical e Intracraniana'
    AND ativo = 1
);

-- [022] Angiotomografia Arterial e Venosa do Tórax e dos Membros Superiores
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Arterial e Venosa do Tórax e dos Membros Superiores',
    'TC',
    'Veias cava superior, braquiocefálicas, subclávias e porções inclusas das jugulares internas pérvias, com trajeto e calibres preservados.
Não há sinais de tromboses venosas nos segmentos inclusos.
Tronco arterial pulmonar e artérias pulmonares principais pérvios e com calibre normal.
Aorta torácica de contornos regulares, com trajeto e calibre preservados. Não há evidências de aneurismas ou dissecções.
Tronco arterial braquiocefálico, artérias subclávias e porções proximais das artérias carótidas comuns pérvias, com trajeto e calibre conservados.
⚠⚠⚠ Como variante da normalidade, o tronco braquiocefálico e a artéria carótida comum esquerda têm origem conjunta no arco aórtico.
⚠⚠⚠ Durante as manobras dinâmicas, não foram observadas reduções significativas de calibre ou compressões extrínsecas significativas sobre as artérias e veias subclávias.
⚠⚠⚠ Sinais de ateromatose difusa, caracterizados por espessamento mediointimal e placas calcificadas esparsas.
Membro superior DIREITO:
Artérias axilar, braquial, radial e ulnar pérvias, com calibre preservado.
Veias axilar, braquial, radiais e ulnares pérvias, com calibre e trajeto preservados.
Veias cefálica e basílica pérvias e com calibre preservado.
Membro superior ESQUERDO:
Artérias axilar, braquial, radial e ulnar pérvias, com calibre preservado.
Veias axilar, braquial, radiais e ulnares pérvias, com calibre e trajeto preservados.
Veias cefálica e basílica pérvias e com calibre preservado.
Demais achados:
Ausência de linfonodomegalias mediastinais ou hilares.
Demais estruturas do mediastino de aspecto preservado.
Traqueia e brônquios pérvios e com calibre preservado.
Parênquima pulmonar com atenuação habitual.
Espaços pleurais livres.
Estruturas ósseas sem alterações relevantes.
Imagens da transição toracoabdominal sem particularidades.',
    '{"indicacao": "", "tecnica": "Imagens obtidas por tecnologia de múltiplos detectores, com a administração do meio de contraste endovenoso.", "achados": "Veias cava superior, braquiocefálicas, subclávias e porções inclusas das jugulares internas pérvias, com trajeto e calibres preservados.\\nNão há sinais de tromboses venosas nos segmentos inclusos.\\nTronco arterial pulmonar e artérias pulmonares principais pérvios e com calibre normal.\\nAorta torácica de contornos regulares, com trajeto e calibre preservados. Não há evidências de aneurismas ou dissecções.\\nTronco arterial braquiocefálico, artérias subclávias e porções proximais das artérias carótidas comuns pérvias, com trajeto e calibre conservados.\\n⚠⚠⚠ Como variante da normalidade, o tronco braquiocefálico e a artéria carótida comum esquerda têm origem conjunta no arco aórtico.\\n⚠⚠⚠ Durante as manobras dinâmicas, não foram observadas reduções significativas de calibre ou compressões extrínsecas significativas sobre as artérias e veias subclávias.\\n⚠⚠⚠ Sinais de ateromatose difusa, caracterizados por espessamento mediointimal e placas calcificadas esparsas.\\nMembro superior DIREITO:\\nArtérias axilar, braquial, radial e ulnar pérvias, com calibre preservado.\\nVeias axilar, braquial, radiais e ulnares pérvias, com calibre e trajeto preservados.\\nVeias cefálica e basílica pérvias e com calibre preservado.\\nMembro superior ESQUERDO:\\nArtérias axilar, braquial, radial e ulnar pérvias, com calibre preservado.\\nVeias axilar, braquial, radiais e ulnares pérvias, com calibre e trajeto preservados.\\nVeias cefálica e basílica pérvias e com calibre preservado.\\nDemais achados:\\nAusência de linfonodomegalias mediastinais ou hilares.\\nDemais estruturas do mediastino de aspecto preservado.\\nTraqueia e brônquios pérvios e com calibre preservado.\\nParênquima pulmonar com atenuação habitual.\\nEspaços pleurais livres.\\nEstruturas ósseas sem alterações relevantes.\\nImagens da transição toracoabdominal sem particularidades.", "impressao": "Exame sem alterações significativas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Arterial e Venosa do Tórax e dos Membros Superiores'
    AND ativo = 1
);

-- [023] Angiotomografia Arterial e Venosa Intracraniana
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Arterial e Venosa Intracraniana',
    'TC',
    'Angio Arterial:
Segmentos intracranianos das artérias carótidas internas, artérias cerebrais anteriores, médias e posteriores de calibre, trajeto e contornos preservados.
Segmento intracraniano das artérias vertebrais e artéria basilar de calibre, trajeto e contornos preservados
Demais artérias intracranianas detectadas por esta técnica apresentando calibre, trajeto e contornos preservados.
Ausência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.
Angio Venosa:
Seio sagital superior, veia de Galeno, veias cerebrais internas, seio reto, seios transversos e sigmóides, e porção cranial das veias jugulares internas de calibre, trajeto e fluxo preservados.
Não foram observados sinais de circulação patológica.
XXX Assimetria do calibre e fluxo dos seios transversos, sendo menor à XX, mais comumente significativo de hipoplasia deste lado.
XXX Seio sagital superior, seio reto, veia cerebral magna e cerebrais internas com fluxo preservado.
XXX Imagens arredondadas junto ao seio XX com atenuação semelhante ao líquor, compatíveis com granulações aracnoídeas.
Bulbos jugulares e porções proximais das veias jugulares internas pérvios.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Angio Arterial:\\nSegmentos intracranianos das artérias carótidas internas, artérias cerebrais anteriores, médias e posteriores de calibre, trajeto e contornos preservados.\\nSegmento intracraniano das artérias vertebrais e artéria basilar de calibre, trajeto e contornos preservados\\nDemais artérias intracranianas detectadas por esta técnica apresentando calibre, trajeto e contornos preservados.\\nAusência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.\\nAngio Venosa:\\nSeio sagital superior, veia de Galeno, veias cerebrais internas, seio reto, seios transversos e sigmóides, e porção cranial das veias jugulares internas de calibre, trajeto e fluxo preservados.\\nNão foram observados sinais de circulação patológica.\\nXXX Assimetria do calibre e fluxo dos seios transversos, sendo menor à XX, mais comumente significativo de hipoplasia deste lado.\\nXXX Seio sagital superior, seio reto, veia cerebral magna e cerebrais internas com fluxo preservado.\\nXXX Imagens arredondadas junto ao seio XX com atenuação semelhante ao líquor, compatíveis com granulações aracnoídeas.\\nBulbos jugulares e porções proximais das veias jugulares internas pérvios.", "impressao": "Exame de angiotomografia arterial e venosa intracraniana sem alterações.\\nNão há evidências de estenoses ou aneurismas.\\nNão há sinais de trombose venosa recente.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Arterial e Venosa Intracraniana'
    AND ativo = 1
);

-- [024] Angiotomografia Arterial e Venosa do Membro Inferior Direito
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Arterial e Venosa do Membro Inferior Direito',
    'TC',
    'Angio ARTERIAL:
Artérias ilíacas comum, externa e interna pérvias e com calibre preservado.
Artérias femorais comum, superficial e profunda pérvias, com calibre preservado.
Artéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior pérvias, sem alterações significativas de calibre.
Angio VENOSA:
Veias ilíacas externa e interna, femorais comuns e profundas, femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Angio ARTERIAL:\\nArtérias ilíacas comum, externa e interna pérvias e com calibre preservado.\\nArtérias femorais comum, superficial e profunda pérvias, com calibre preservado.\\nArtéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior pérvias, sem alterações significativas de calibre.\\nAngio VENOSA:\\nVeias ilíacas externa e interna, femorais comuns e profundas, femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.", "impressao": "Exame sem alterações significativas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Arterial e Venosa do Membro Inferior Direito'
    AND ativo = 1
);

-- [025] Angiotomografia Arterial e Venosa dos Membros Superiores
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Arterial e Venosa dos Membros Superiores',
    'TC',
    'Membro superior DIREITO:
Artérias subclávia, axilar, braquial, radial e ulnar pérvias, com calibre preservado.
Veias subclávia, axilar, braquial, radiais e ulnares pérvias, com calibre e trajeto preservados.
Veias cefálica e basílica pérvias e com calibre preservado.
Membro superior ESQUERDO:
Artérias subclávia, axilar, braquial, radial e ulnar pérvias, com calibre preservado.
Veias subclávia, axilar, braquial, radiais e ulnares pérvias, com calibre e trajeto preservados.
Veias cefálica e basílica pérvias e com calibre preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Membro superior DIREITO:\\nArtérias subclávia, axilar, braquial, radial e ulnar pérvias, com calibre preservado.\\nVeias subclávia, axilar, braquial, radiais e ulnares pérvias, com calibre e trajeto preservados.\\nVeias cefálica e basílica pérvias e com calibre preservado.\\nMembro superior ESQUERDO:\\nArtérias subclávia, axilar, braquial, radial e ulnar pérvias, com calibre preservado.\\nVeias subclávia, axilar, braquial, radiais e ulnares pérvias, com calibre e trajeto preservados.\\nVeias cefálica e basílica pérvias e com calibre preservado.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Arterial e Venosa dos Membros Superiores'
    AND ativo = 1
);

-- [026] Angiotomografia Arterial e Venosa do Membro Superior Direito
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Arterial e Venosa do Membro Superior Direito',
    'TC',
    'Artérias subclávia, axilar, braquial, radial e ulnar pérvias, com calibre preservado.
Veias subclávia, axilar, braquial, radiais e ulnares pérvias, com calibre e trajeto preservados.
Veias cefálica e basílica pérvias e com calibre preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Artérias subclávia, axilar, braquial, radial e ulnar pérvias, com calibre preservado.\\nVeias subclávia, axilar, braquial, radiais e ulnares pérvias, com calibre e trajeto preservados.\\nVeias cefálica e basílica pérvias e com calibre preservado.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Arterial e Venosa do Membro Superior Direito'
    AND ativo = 1
);

-- [027] Angiotomografia Arterial Intracraniana
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Arterial Intracraniana',
    'TC',
    'Segmentos intracranianos das artérias carótidas internas, artérias cerebrais anteriores, médias e posteriores de calibre, trajeto e contornos preservados.
Segmento intracraniano das artérias vertebrais e artéria basilar de calibre, trajeto e contornos preservados
Demais artérias intracranianas detectadas por esta técnica apresentando calibre, trajeto e contornos preservados.
Ausência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Segmentos intracranianos das artérias carótidas internas, artérias cerebrais anteriores, médias e posteriores de calibre, trajeto e contornos preservados.\\nSegmento intracraniano das artérias vertebrais e artéria basilar de calibre, trajeto e contornos preservados\\nDemais artérias intracranianas detectadas por esta técnica apresentando calibre, trajeto e contornos preservados.\\nAusência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.", "impressao": "Exame dentro dos padrões da normalidade.\\nNão há evidências de estenoses ou aneurismas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Arterial Intracraniana'
    AND ativo = 1
);

-- [028] Angiotomografia Computadorizada do Membro Inferior Direito
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada do Membro Inferior Direito',
    'TC',
    'Sinais de ateromatose moderada caracterizada por espessamento e calcificações parietais esparsas
no leito estudado.
Artérias ilíacas, pérvias e com calibre preservado.
Artérias femorais comum, superficial e profunda pérvias, com calibre preservado.
Artéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior
pérvias, sem alterações significativas de calibre.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Sinais de ateromatose moderada caracterizada por espessamento e calcificações parietais esparsas\\nno leito estudado.\\nArtérias ilíacas, pérvias e com calibre preservado.\\nArtérias femorais comum, superficial e profunda pérvias, com calibre preservado.\\nArtéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior\\npérvias, sem alterações significativas de calibre.", "impressao": "Exame sem alterações significativas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada do Membro Inferior Direito'
    AND ativo = 1
);

-- [029] Angiotomografia Computadorizada do Membro Inferior Esquerdo
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada do Membro Inferior Esquerdo',
    'TC',
    'Sinais de ateromatose moderada caracterizada por espessamento e calcificações parietais esparsas
no leito estudado.
Artérias ilíacas, pérvias e com calibre preservado.
Artérias femorais comum, superficial e profunda pérvias, com calibre preservado.
Artéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior
pérvias, sem alterações significativas de calibre.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Sinais de ateromatose moderada caracterizada por espessamento e calcificações parietais esparsas\\nno leito estudado.\\nArtérias ilíacas, pérvias e com calibre preservado.\\nArtérias femorais comum, superficial e profunda pérvias, com calibre preservado.\\nArtéria poplítea, tronco tibiofibular, artérias tibial anterior, tibial posterior, fibular e pediosa anterior\\npérvias, sem alterações significativas de calibre.", "impressao": "Exame sem alterações significativas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada do Membro Inferior Esquerdo'
    AND ativo = 1
);

-- [030] Angiotomografia Arterial dos Membros Superiores
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Arterial dos Membros Superiores',
    'TC',
    'Membro superior DIREITO:
Artérias subclávia, axilar, braquial, radial e ulnar pérvias, com calibre preservado.
Membro superior ESQUERDO:
Artérias subclávia, axilar, braquial, radial e ulnar pérvias, com calibre preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Membro superior DIREITO:\\nArtérias subclávia, axilar, braquial, radial e ulnar pérvias, com calibre preservado.\\nMembro superior ESQUERDO:\\nArtérias subclávia, axilar, braquial, radial e ulnar pérvias, com calibre preservado.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Arterial dos Membros Superiores'
    AND ativo = 1
);

-- [031] Angiotomografia Computadorizada da Pelve
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada da Pelve',
    'TC',
    'Artérias ilíacas comuns, externas e internas, femorais comuns e segmento proximal das femorais superficiais e profundas pérvios, sem alterações significativas de calibre.
Ateromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.
*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.
- Bifurcação aórtica: XXX cm
- Artéria ilíaca comum direita pré bifurcação: XXXXX cm de diâmetro e XXX cm de extensão
- Artéria ilíaca externa direita : XXX cm
- Artéria ilíaca comum esquerda pré bifurcação: XXX cm de diâmetro e XXX cm de extensão
- Artéria ilíaca externa esquerda : XXX cm',
    '{"indicacao": "", "tecnica": "Exame realizado com aquisição multislice, antes e após a injeção intravenosa do meio de contraste iodado.", "achados": "Artérias ilíacas comuns, externas e internas, femorais comuns e segmento proximal das femorais superficiais e profundas pérvios, sem alterações significativas de calibre.\\nAteromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.\\n*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.\\n- Bifurcação aórtica: XXX cm\\n- Artéria ilíaca comum direita pré bifurcação: XXXXX cm de diâmetro e XXX cm de extensão\\n- Artéria ilíaca externa direita : XXX cm\\n- Artéria ilíaca comum esquerda pré bifurcação: XXX cm de diâmetro e XXX cm de extensão\\n- Artéria ilíaca externa esquerda : XXX cm", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada da Pelve'
    AND ativo = 1
);

-- [032] Angiotomografia Computadorizada das Artérias Ilíacas
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada das Artérias Ilíacas',
    'TC',
    'Artérias ilíacas comuns, externas e internas, femorais comuns e segmento proximal das femorais superficiais e profundas pérvios, sem alterações significativas de calibre.
Ateromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.
*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.
- Bifurcação aórtica: XXX cm
- Artéria ilíaca comum direita pré bifurcação: XXXXX cm de diâmetro e XXX cm de extensão
- Artéria ilíaca externa direita : XXX cm
- Artéria ilíaca comum esquerda pré bifurcação: XXX cm de diâmetro e XXX cm de extensão
- Artéria ilíaca externa esquerda : XXX cm',
    '{"indicacao": "", "tecnica": "Exame realizado com aquisição multislice, antes e após a injeção intravenosa do meio de contraste iodado.", "achados": "Artérias ilíacas comuns, externas e internas, femorais comuns e segmento proximal das femorais superficiais e profundas pérvios, sem alterações significativas de calibre.\\nAteromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.\\n*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.\\n- Bifurcação aórtica: XXX cm\\n- Artéria ilíaca comum direita pré bifurcação: XXXXX cm de diâmetro e XXX cm de extensão\\n- Artéria ilíaca externa direita : XXX cm\\n- Artéria ilíaca comum esquerda pré bifurcação: XXX cm de diâmetro e XXX cm de extensão\\n- Artéria ilíaca externa esquerda : XXX cm", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada das Artérias Ilíacas'
    AND ativo = 1
);

-- [033] Angiotomografia Computadorizada das Artérias Renais
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada das Artérias Renais',
    'TC',
    'Artérias renais únicas, pérvias e com calibre preservado.
Aorta abdominal pérvia, com trajeto e calibre preservados.
Tronco celíaco, artérias mesentéricas superior e inferior pérvias e com calibre preservado.
Ateromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.
*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.
*** Aneurisma fusiforme da aorta abdominal que se estende inferiormente até o plano da bifurcação aórtica. Emergem do aneurisma as artérias XXXXX, que se apresentam pérvias e com calibre habitual. Medidas relevantes descritas abaixo.
*** Nota-se ainda, trombose mural na ao longo da luz do segmento dilatado.
Diâmetros máximos da aorta:
- Transição toracoabdominal: XXX cm
- Aorta abdominal suprarrenal: XXX cm
- Aorta abdominal infrarrenal: XXX cm',
    '{"indicacao": "", "tecnica": "Exame realizado com aquisição multislice, antes e após a injeção intravenosa do meio de contraste iodado.", "achados": "Artérias renais únicas, pérvias e com calibre preservado.\\nAorta abdominal pérvia, com trajeto e calibre preservados.\\nTronco celíaco, artérias mesentéricas superior e inferior pérvias e com calibre preservado.\\nAteromatose difusa, com irregularidades e placas parietais parcialmente calcificadas nos segmentos arteriais incluídos no estudo.\\n*** Notam-se irregularidades da superfície luminal, indicativas de ulcerações.\\n*** Aneurisma fusiforme da aorta abdominal que se estende inferiormente até o plano da bifurcação aórtica. Emergem do aneurisma as artérias XXXXX, que se apresentam pérvias e com calibre habitual. Medidas relevantes descritas abaixo.\\n*** Nota-se ainda, trombose mural na ao longo da luz do segmento dilatado.\\nDiâmetros máximos da aorta:\\n- Transição toracoabdominal: XXX cm\\n- Aorta abdominal suprarrenal: XXX cm\\n- Aorta abdominal infrarrenal: XXX cm", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada das Artérias Renais'
    AND ativo = 1
);

-- [034] Angiotomografia das Artérias Pulmonares Protocolo Dirigido para Pesquisa de Trom
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia das Artérias Pulmonares Protocolo Dirigido para Pesquisa de Tromboembolismo Pulmonar (tep)',
    'TC',
    'Ausência de sinais de tromboembolismo pulmonar agudo.
Tronco da artéria pulmonar e artérias pulmonares principais pérvios e com calibre normal.
Demais estruturas vasculares mediastinais com trajeto e calibre conservados.
Ausência de linfonodomegalias mediastinais ou hilares.
Traqueia e brônquios pérvios e com calibre preservado.
Parênquima pulmonar com atenuação habitual.
Ausência de derrame pleural.
Estruturas ósseas sem alterações relevantes.',
    '{"indicacao": "", "tecnica": "Imagens obtidas por tecnologia de múltiplos detectores, após a administração do meio de contraste endovenoso.", "achados": "Ausência de sinais de tromboembolismo pulmonar agudo.\\nTronco da artéria pulmonar e artérias pulmonares principais pérvios e com calibre normal.\\nDemais estruturas vasculares mediastinais com trajeto e calibre conservados.\\nAusência de linfonodomegalias mediastinais ou hilares.\\nTraqueia e brônquios pérvios e com calibre preservado.\\nParênquima pulmonar com atenuação habitual.\\nAusência de derrame pleural.\\nEstruturas ósseas sem alterações relevantes.", "impressao": "Ausência de sinais de tromboembolismo pulmonar agudo (TEP).", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia das Artérias Pulmonares Protocolo Dirigido para Pesquisa de Tromboembolismo Pulmonar (tep)'
    AND ativo = 1
);

-- [035] Angiotomografia Computadorizada Arterial e Venosa do Tórax e Bilateral dos Braço
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada Arterial e Venosa do Tórax e Bilateral dos Braços para Síndrome do Desfiladeiro Torácico',
    'TC',
    'Tronco arterial braquiocefálico, artérias carótidas comuns, subclávias, axilares e braquiais pérvias, com trajeto e calibre conservados.
<<<⚠️⚠️>>> Como variante da normalidade, o tronco braquiocefálico e a artéria carótida comum esquerda têm origem conjunta no arco aórtico.
Durante as manobras dinâmicas, não foram observadas reduções significativas dos calibres ou compressões extrínsecas sobre as artérias subclávias.
Veias cava superior, braquicefálicas, jugulares internas, subclávias, axilares e braquiais pérvias, sem alterações significativas de calibre ou tromboses.
Durante as manobras dinâmicas, não foram observadas reduções significativas do calibre das veias subclávias.
Achados adicionais: <<<⚠️⚠️>>>',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.\\nForam realizadas manobras para a pesquisa de síndrome do desfiladeiro torácico.", "achados": "Tronco arterial braquiocefálico, artérias carótidas comuns, subclávias, axilares e braquiais pérvias, com trajeto e calibre conservados.\\n<<<⚠️⚠️>>> Como variante da normalidade, o tronco braquiocefálico e a artéria carótida comum esquerda têm origem conjunta no arco aórtico.\\nDurante as manobras dinâmicas, não foram observadas reduções significativas dos calibres ou compressões extrínsecas sobre as artérias subclávias.\\nVeias cava superior, braquicefálicas, jugulares internas, subclávias, axilares e braquiais pérvias, sem alterações significativas de calibre ou tromboses.\\nDurante as manobras dinâmicas, não foram observadas reduções significativas do calibre das veias subclávias.\\nAchados adicionais: <<<⚠️⚠️>>>", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada Arterial e Venosa do Tórax e Bilateral dos Braços para Síndrome do Desfiladeiro Torácico'
    AND ativo = 1
);

-- [036] Angiotomografia Computadorizada Arterial e Venosa do Tórax e do Braço Direito pa
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada Arterial e Venosa do Tórax e do Braço Direito para Síndrome do Desfiladeiro Torácico',
    'TC',
    'Tronco arterial braquiocefálico, e artérias carótida comum, subclávia, axilar e braquial direitas pérvias, com trajeto e calibre conservados.
<<<⚠️⚠️>>> Como variante da normalidade, o tronco braquiocefálico e a artéria carótida comum esquerda têm origem conjunta no arco aórtico.
Durante as manobras dinâmicas, não foram observadas reduções significativas dos calibres ou compressões extrínsecas sobre a artéria subclávia.
Veias cava superior, e veias braquicefálica, jugular interna, subclávia, axilar e braquial direitas pérvias, sem alterações significativas de calibre ou tromboses.
Durante as manobras dinâmicas, não foram observadas reduções significativas do calibre da veia subclávia.
Achados adicionais: <<<⚠️⚠️>>>',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.\\nForam realizadas manobras para a pesquisa de síndrome do desfiladeiro torácico.", "achados": "Tronco arterial braquiocefálico, e artérias carótida comum, subclávia, axilar e braquial direitas pérvias, com trajeto e calibre conservados.\\n<<<⚠️⚠️>>> Como variante da normalidade, o tronco braquiocefálico e a artéria carótida comum esquerda têm origem conjunta no arco aórtico.\\nDurante as manobras dinâmicas, não foram observadas reduções significativas dos calibres ou compressões extrínsecas sobre a artéria subclávia.\\nVeias cava superior, e veias braquicefálica, jugular interna, subclávia, axilar e braquial direitas pérvias, sem alterações significativas de calibre ou tromboses.\\nDurante as manobras dinâmicas, não foram observadas reduções significativas do calibre da veia subclávia.\\nAchados adicionais: <<<⚠️⚠️>>>", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada Arterial e Venosa do Tórax e do Braço Direito para Síndrome do Desfiladeiro Torácico'
    AND ativo = 1
);

-- [037] Angiotomografia Computadorizada Venosa das Veias Cava Inferior e Ilíacas
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada Venosa das Veias Cava Inferior e Ilíacas',
    'TC',
    'Veias cava inferior, ilíacas comuns, internas e externas pérvias, de calibre e trajeto preservado.
Tronco da veia porta e ramos portais principais pérvios, de calibre e trajeto preservados.
Veias hepáticas, mesentérica superior e esplênica pérvias, de calibre e trajeto preservados.
Veias renais pérvias e anatômicas.',
    '{"indicacao": "", "tecnica": "Exame realizado com aquisição multislice, com a injeção intravenosa do meio de contraste iodado.", "achados": "Veias cava inferior, ilíacas comuns, internas e externas pérvias, de calibre e trajeto preservado.\\nTronco da veia porta e ramos portais principais pérvios, de calibre e trajeto preservados.\\nVeias hepáticas, mesentérica superior e esplênica pérvias, de calibre e trajeto preservados.\\nVeias renais pérvias e anatômicas.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada Venosa das Veias Cava Inferior e Ilíacas'
    AND ativo = 1
);

-- [038] Angiotomografia Computadorizada Venosa do Abdome Superior
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada Venosa do Abdome Superior',
    'TC',
    'Veias cava inferior de calibre e trajeto preservado.
Tronco da veia porta e ramos portais principais pérvios, de calibre e trajeto preservados.
Veias hepáticas, mesentérica superior e esplênica pérvias, de calibre e trajeto preservados.
Veias renais pérvias e anatômicas.',
    '{"indicacao": "", "tecnica": "Exame realizado com aquisição multislice, antes e após a injeção intravenosa do meio de contraste iodado.", "achados": "Veias cava inferior de calibre e trajeto preservado.\\nTronco da veia porta e ramos portais principais pérvios, de calibre e trajeto preservados.\\nVeias hepáticas, mesentérica superior e esplênica pérvias, de calibre e trajeto preservados.\\nVeias renais pérvias e anatômicas.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada Venosa do Abdome Superior'
    AND ativo = 1
);

-- [039] Angiotomografia das Veias Cava Inferior e Ilíacas, e Venosa dos Membros Inferior
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia das Veias Cava Inferior e Ilíacas, e Venosa dos Membros Inferiores',
    'TC',
    'Veias cava inferior, ilíacas comuns, internas e externas pérvias, de calibre e trajeto preservado.
Membro inferior DIREITO:
Veias femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.
Membro inferior ESQUERDO:
Veias femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Veias cava inferior, ilíacas comuns, internas e externas pérvias, de calibre e trajeto preservado.\\nMembro inferior DIREITO:\\nVeias femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.\\nMembro inferior ESQUERDO:\\nVeias femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia das Veias Cava Inferior e Ilíacas, e Venosa dos Membros Inferiores'
    AND ativo = 1
);

-- [040] Angiotomografia Computadorizada Venosa do Abdome Superior e Pelve
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada Venosa do Abdome Superior e Pelve',
    'TC',
    'Veias cava inferior, ilíacas comuns, internas e externas pérvias, de calibre e trajeto preservado.
Tronco da veia porta e ramos portais principais pérvios, de calibre e trajeto preservados.
Veias hepáticas, mesentérica superior e esplênica pérvias, de calibre e trajeto preservados.
Veias renais pérvias e anatômicas.',
    '{"indicacao": "", "tecnica": "Exame realizado com aquisição multislice, antes e após a injeção intravenosa do meio de contraste iodado.", "achados": "Veias cava inferior, ilíacas comuns, internas e externas pérvias, de calibre e trajeto preservado.\\nTronco da veia porta e ramos portais principais pérvios, de calibre e trajeto preservados.\\nVeias hepáticas, mesentérica superior e esplênica pérvias, de calibre e trajeto preservados.\\nVeias renais pérvias e anatômicas.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada Venosa do Abdome Superior e Pelve'
    AND ativo = 1
);

-- [041] Angiotomografia Venosa Cervical e Intracraniana
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Venosa Cervical e Intracraniana',
    'TC',
    'Seio sagital superior, veia de Galeno, veias cerebrais internas, seio reto, seios transversos e sigmóides, e porção cranial das veias jugulares internas de calibre, trajeto e fluxo preservados.
Não foram observados sinais de circulação patológica.
XXX Assimetria do calibre e fluxo dos seios transversos, sendo menor à XX, mais comumente significativo de hipoplasia deste lado.
XXX Seio sagital superior, seio reto, veia cerebral magna e cerebrais internas com fluxo preservado.
XXX Imagens arredondadas junto ao seio XX com atenuação semelhante ao líquor em todas as sequências, compatíveis com granulações aracnoídeas.
Bulbos jugulares e veias jugulares internas pérvios.
Veias subclávias e segmentos incluídos no estudo (proximais) das veias axilares pérvios.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Seio sagital superior, veia de Galeno, veias cerebrais internas, seio reto, seios transversos e sigmóides, e porção cranial das veias jugulares internas de calibre, trajeto e fluxo preservados.\\nNão foram observados sinais de circulação patológica.\\nXXX Assimetria do calibre e fluxo dos seios transversos, sendo menor à XX, mais comumente significativo de hipoplasia deste lado.\\nXXX Seio sagital superior, seio reto, veia cerebral magna e cerebrais internas com fluxo preservado.\\nXXX Imagens arredondadas junto ao seio XX com atenuação semelhante ao líquor em todas as sequências, compatíveis com granulações aracnoídeas.\\nBulbos jugulares e veias jugulares internas pérvios.\\nVeias subclávias e segmentos incluídos no estudo (proximais) das veias axilares pérvios.", "impressao": "Exame de angiotomografia venosa cervical e intracraniana dentro dos padrões da normalidade, sem evidência de trombose venosa recente.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Venosa Cervical e Intracraniana'
    AND ativo = 1
);

-- [042] Angiotomografia Venosa de Tórax
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Venosa de Tórax',
    'TC',
    'Veias cava superior, braquiocefálicas, subclávias e porções das jugulares internas e da cava inferior incluídas no estudo pérvias, com calibre e trajeto preservados.
Demais estruturas vasculares mediastinais com trajeto e calibre conservados.
Ausência de linfonodomegalias mediastinais ou hilares.
Traqueia e brônquios pérvios e com calibre preservado.
Parênquima pulmonar com atenuação habitual.
Espaços pleurais livres.
Estruturas ósseas sem alterações relevantes.',
    '{"indicacao": "", "tecnica": "Imagens obtidas por tecnologia de múltiplos detectores, após a administração do meio de contraste endovenoso.", "achados": "Veias cava superior, braquiocefálicas, subclávias e porções das jugulares internas e da cava inferior incluídas no estudo pérvias, com calibre e trajeto preservados.\\nDemais estruturas vasculares mediastinais com trajeto e calibre conservados.\\nAusência de linfonodomegalias mediastinais ou hilares.\\nTraqueia e brônquios pérvios e com calibre preservado.\\nParênquima pulmonar com atenuação habitual.\\nEspaços pleurais livres.\\nEstruturas ósseas sem alterações relevantes.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Venosa de Tórax'
    AND ativo = 1
);

-- [043] Angiotomografia Venosa de Tórax e dos Membros Superiores (protocolo para a Pesqu
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Venosa de Tórax e dos Membros Superiores (protocolo para a Pesquisa de Acesso Venoso)',
    'TC',
    'Veia cava superior, troncos braquiocefálicos, veias subclávias, axilares, braquiais, radiais e ulnares pérvias, com calibre e trajeto preservados.
Tronco arterial pulmonar e artérias pulmonares principais pérvios e com calibre normal.
Demais estruturas vasculares mediastinais com trajeto e calibre conservados.
Ausência de linfonodomegalias mediastinais ou hilares.
Traqueia e brônquios pérvios e com calibre preservado.
Parênquima pulmonar com atenuação habitual.
Espaços pleurais livres.
Estruturas ósseas sem alterações relevantes.',
    '{"indicacao": "", "tecnica": "Imagens obtidas por tecnologia de múltiplos detectores, após a administração do meio de contraste endovenoso.", "achados": "Veia cava superior, troncos braquiocefálicos, veias subclávias, axilares, braquiais, radiais e ulnares pérvias, com calibre e trajeto preservados.\\nTronco arterial pulmonar e artérias pulmonares principais pérvios e com calibre normal.\\nDemais estruturas vasculares mediastinais com trajeto e calibre conservados.\\nAusência de linfonodomegalias mediastinais ou hilares.\\nTraqueia e brônquios pérvios e com calibre preservado.\\nParênquima pulmonar com atenuação habitual.\\nEspaços pleurais livres.\\nEstruturas ósseas sem alterações relevantes.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Venosa de Tórax e dos Membros Superiores (protocolo para a Pesquisa de Acesso Venoso)'
    AND ativo = 1
);

-- [044] Angiotomografia Venosa de Tórax e do Membro Superior Direito (protocolo para a P
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Venosa de Tórax e do Membro Superior Direito (protocolo para a Pesquisa de Acesso Venoso)',
    'TC',
    'Veia cava superior, tronco braquiocefálico, veias subclávia, axilar, braquiais, radiais e ulnares pérvias, com calibre e trajeto preservados.
Tronco arterial pulmonar e artérias pulmonares principais pérvios e com calibre normal.
Demais estruturas vasculares mediastinais com trajeto e calibre conservados.
Ausência de linfonodomegalias mediastinais ou hilares.
Traqueia e brônquios pérvios e com calibre preservado.
Parênquima pulmonar com atenuação habitual.
Espaços pleurais livres.
Estruturas ósseas sem alterações relevantes.',
    '{"indicacao": "", "tecnica": "Imagens obtidas por tecnologia de múltiplos detectores, após a administração do meio de contraste endovenoso.", "achados": "Veia cava superior, tronco braquiocefálico, veias subclávia, axilar, braquiais, radiais e ulnares pérvias, com calibre e trajeto preservados.\\nTronco arterial pulmonar e artérias pulmonares principais pérvios e com calibre normal.\\nDemais estruturas vasculares mediastinais com trajeto e calibre conservados.\\nAusência de linfonodomegalias mediastinais ou hilares.\\nTraqueia e brônquios pérvios e com calibre preservado.\\nParênquima pulmonar com atenuação habitual.\\nEspaços pleurais livres.\\nEstruturas ósseas sem alterações relevantes.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Venosa de Tórax e do Membro Superior Direito (protocolo para a Pesquisa de Acesso Venoso)'
    AND ativo = 1
);

-- [045] Angiotomografia Venosa Intracraniana
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Venosa Intracraniana',
    'TC',
    'Seio sagital superior, veia de Galeno, veias cerebrais internas, seio reto, seios transversos e sigmóides, e porção cranial das veias jugulares internas de calibre, trajeto e fluxo preservados.
Não foram observados sinais de circulação patológica.
XXX Assimetria do calibre e fluxo dos seios transversos, sendo menor à XX, mais comumente significativo de hipoplasia deste lado.
XXX Seio sagital superior, seio reto, veia cerebral magna e cerebrais internas com fluxo preservado.
XXX Imagens arredondadas junto ao seio XX com atenuação semelhante ao líquor, compatíveis com granulações aracnoídeas.
Bulbos jugulares e porções proximais das veias jugulares internas pérvios.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Seio sagital superior, veia de Galeno, veias cerebrais internas, seio reto, seios transversos e sigmóides, e porção cranial das veias jugulares internas de calibre, trajeto e fluxo preservados.\\nNão foram observados sinais de circulação patológica.\\nXXX Assimetria do calibre e fluxo dos seios transversos, sendo menor à XX, mais comumente significativo de hipoplasia deste lado.\\nXXX Seio sagital superior, seio reto, veia cerebral magna e cerebrais internas com fluxo preservado.\\nXXX Imagens arredondadas junto ao seio XX com atenuação semelhante ao líquor, compatíveis com granulações aracnoídeas.\\nBulbos jugulares e porções proximais das veias jugulares internas pérvios.", "impressao": "Exame dentro dos padrões da normalidade.\\nNão há sinais de trombose venosa recente.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Venosa Intracraniana'
    AND ativo = 1
);

-- [046] Angiotomografia Venosa dos Membros Inferiores
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Venosa dos Membros Inferiores',
    'TC',
    'Membro inferior DIREITO:
Veias ilíacas externa e interna, femorais comuns e profundas, femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.
Membro inferior ESQUERDO:
Veias ilíacas externa e interna, femorais comuns e profundas, femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Membro inferior DIREITO:\\nVeias ilíacas externa e interna, femorais comuns e profundas, femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.\\nMembro inferior ESQUERDO:\\nVeias ilíacas externa e interna, femorais comuns e profundas, femorais, poplíteas, tronco tibiofibular, veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Venosa dos Membros Inferiores'
    AND ativo = 1
);

-- [047] Angiotomografia Computadorizada Venosa do Membro Inferior Direito
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada Venosa do Membro Inferior Direito',
    'TC',
    'Veias ilíacas externa e interna, femorais comuns e profundas, femorais, poplíteas, tronco tibiofibular,
veias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Veias ilíacas externa e interna, femorais comuns e profundas, femorais, poplíteas, tronco tibiofibular,\\nveias tibiais anteriores, posteriores e fibulares pérvias, de calibre e trajeto preservados.", "impressao": "Exame sem alterações significativas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada Venosa do Membro Inferior Direito'
    AND ativo = 1
);

-- [048] Angiotomografia Venosa dos Membros Superiores
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Venosa dos Membros Superiores',
    'TC',
    'Membro superior DIREITO:
Veias subclávia, axilar, braquial, radiais e ulnares pérvias, com calibre e trajeto preservados.
Veias cefálica e basílica pérvias e com calibre preservado.
Membro superior ESQUERDO:
Veias subclávia, axilar, braquial, radiais e ulnares pérvias, com calibre e trajeto preservados.
Veias cefálica e basílica pérvias e com calibre preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Membro superior DIREITO:\\nVeias subclávia, axilar, braquial, radiais e ulnares pérvias, com calibre e trajeto preservados.\\nVeias cefálica e basílica pérvias e com calibre preservado.\\nMembro superior ESQUERDO:\\nVeias subclávia, axilar, braquial, radiais e ulnares pérvias, com calibre e trajeto preservados.\\nVeias cefálica e basílica pérvias e com calibre preservado.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Venosa dos Membros Superiores'
    AND ativo = 1
);

-- [049] Angiotomografia Computadorizada Venosa do Membro Superior Esquerdo
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada Venosa do Membro Superior Esquerdo',
    'TC',
    'Veias subclávia, axilares, braquiais, radiais e ulnares pérvias, de calibe e trajeto preservadas.
Veias cefálica e basílica sem particularidades neste estudo.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Veias subclávia, axilares, braquiais, radiais e ulnares pérvias, de calibe e trajeto preservadas.\\nVeias cefálica e basílica sem particularidades neste estudo.", "impressao": "Exame sem alterações significativas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada Venosa do Membro Superior Esquerdo'
    AND ativo = 1
);

-- [050] Angiotomografia Computadorizada Venosa da Pelve
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia Computadorizada Venosa da Pelve',
    'TC',
    'Veias cava inferior, ilíacas comuns, internas e externas pérvias, de calibre e trajeto preservado.',
    '{"indicacao": "", "tecnica": "Exame realizado com aquisição multislice, antes e após a injeção intravenosa do meio de contraste iodado.", "achados": "Veias cava inferior, ilíacas comuns, internas e externas pérvias, de calibre e trajeto preservado.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia Computadorizada Venosa da Pelve'
    AND ativo = 1
);

-- [051] Tomografia Computadorizada da Pelve com Cistografia
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada da Pelve com Cistografia',
    'TC',
    'Bexiga com boa repleção, paredes regulares e conteúdo homogêneo.
Demais achados:
Porções do fígado, do pâncreas, do baço e dos rins inclusas no estudo sem particularidades.
Não há evidência de cálculos ou dilatação das porções dos ureteres inclusas no estudo.
Aorta de trajeto e calibre preservados.
Útero e regiões anexiais sem anormalidades detectáveis, ressalvas feitas à avaliação limitada ao método. A critério clínico, o estudo ultrassonográfico ou de ressonância magnética podem trazer informações adicionais.
<<<⚠️⚠️>>> Apêndice cecal de aspecto preservado.
<<<⚠️⚠️>>> Apêndice cecal não caracterizado. Não foram identificadas alterações inflamatórias pericecais.
Ausência de linfonodomegalias ou de líquido livre no andar inferior do abdome.
Estruturas ósseas sem alterações relevantes.',
    '{"indicacao": "", "tecnica": "Imagens obtidas antes e após a administração intravenosa de meio de contraste iodado.\\nRealizada administração de contraste iodado por via intravesical para opacificação da bexiga urinária.", "achados": "Bexiga com boa repleção, paredes regulares e conteúdo homogêneo.\\nDemais achados:\\nPorções do fígado, do pâncreas, do baço e dos rins inclusas no estudo sem particularidades.\\nNão há evidência de cálculos ou dilatação das porções dos ureteres inclusas no estudo.\\nAorta de trajeto e calibre preservados.\\nÚtero e regiões anexiais sem anormalidades detectáveis, ressalvas feitas à avaliação limitada ao método. A critério clínico, o estudo ultrassonográfico ou de ressonância magnética podem trazer informações adicionais.\\n<<<⚠️⚠️>>> Apêndice cecal de aspecto preservado.\\n<<<⚠️⚠️>>> Apêndice cecal não caracterizado. Não foram identificadas alterações inflamatórias pericecais.\\nAusência de linfonodomegalias ou de líquido livre no andar inferior do abdome.\\nEstruturas ósseas sem alterações relevantes.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada da Pelve com Cistografia'
    AND ativo = 1
);

-- [052] Enterografia por Tomografia Computadorizada do Abdome e Pelve
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Enterografia por Tomografia Computadorizada do Abdome e Pelve',
    'TC',
    'Houve progressão do meio de contraste administrado por via oral até o ____, com distensão satisfatória das alças delgadas.
Alças intestinais delgadas com paredes de aspecto preservado, calibre e distribuição habituais. Não há sinais de estenose ou dilatação intestinal.
Ausência de fístulas ou coleções.
A gordura intracavitária tem aspecto preservado.
A análise do cólon é limitada neste protocolo de exame, porém não se identificam alterações expressivas evidentes.
<⚠️⚠️> Apêndice cecal de aspecto preservado.
<⚠️⚠️> Apêndice cecal não caracterizado. Não foram identificadas alterações inflamatórias pericecais.
Não há sinais de líquido livre intra-abdominal.
Ausência de linfonodomegalias.
<⚠️⚠️>  Sinais de doença inflamatória em atividade em segmentos descontínuos de intestino delgado, caracterizados por espessamento parietal, hiperrealce mucoso e ingurgitamento dos vasos retos, destacando-se:
- segmento de intestino ____, com extensão de até ___ cm, sem/com componente estenosante
- segmento de intestino ____, com extensão de até ___ cm, sem/com componente estenosante
<⚠️⚠️>  Densificação da gordura mesentérica / ingurgitamento dos vasos retos / proliferação da gordura mesentérica) adjacentes aos segmentos envolvidos.
<⚠️⚠️>  Sinais de doença fistulizante (ativa / inativa) [relatar o tipo e localização da fístula].
Fígado, baço, pâncreas, adrenais, rins e bexiga com morfologia e atenuação preservadas.
Ausência de dilatação das vias biliares.',
    '{"indicacao": "", "tecnica": "Foram realizadas aquisições com a injeção do meio de contraste iodado endovenoso. Foi administrado por via oral solução neutra para distensão de alças intestinais delgadas.", "achados": "Houve progressão do meio de contraste administrado por via oral até o ____, com distensão satisfatória das alças delgadas.\\nAlças intestinais delgadas com paredes de aspecto preservado, calibre e distribuição habituais. Não há sinais de estenose ou dilatação intestinal.\\nAusência de fístulas ou coleções.\\nA gordura intracavitária tem aspecto preservado.\\nA análise do cólon é limitada neste protocolo de exame, porém não se identificam alterações expressivas evidentes.\\n<⚠️⚠️> Apêndice cecal de aspecto preservado.\\n<⚠️⚠️> Apêndice cecal não caracterizado. Não foram identificadas alterações inflamatórias pericecais.\\nNão há sinais de líquido livre intra-abdominal.\\nAusência de linfonodomegalias.\\n<⚠️⚠️>  Sinais de doença inflamatória em atividade em segmentos descontínuos de intestino delgado, caracterizados por espessamento parietal, hiperrealce mucoso e ingurgitamento dos vasos retos, destacando-se:\\n- segmento de intestino ____, com extensão de até ___ cm, sem/com componente estenosante\\n- segmento de intestino ____, com extensão de até ___ cm, sem/com componente estenosante\\n<⚠️⚠️>  Densificação da gordura mesentérica / ingurgitamento dos vasos retos / proliferação da gordura mesentérica) adjacentes aos segmentos envolvidos.\\n<⚠️⚠️>  Sinais de doença fistulizante (ativa / inativa) [relatar o tipo e localização da fístula].\\nFígado, baço, pâncreas, adrenais, rins e bexiga com morfologia e atenuação preservadas.\\nAusência de dilatação das vias biliares.", "impressao": "#### Exame dentro dos parâmetros da normalidade.\\n#### Sinais de doença inflamatória em atividade estenosante / não estenosante em segmentos de alça intestinal ___ sem/com fístula.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Enterografia por Tomografia Computadorizada do Abdome e Pelve'
    AND ativo = 1
);

-- [053] Tomografia por Emissão de Pósitrons Tomografia Computadorizada do Corpo (pet-ct)
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia por Emissão de Pósitrons Tomografia Computadorizada do Corpo (pet-ct)',
    'TC',
    '',
    '{"indicacao": "", "tecnica": "", "achados": "", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia por Emissão de Pósitrons Tomografia Computadorizada do Corpo (pet-ct)'
    AND ativo = 1
);

-- [054] Radiofármaco: 18fdg. Dose: Mci.
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Radiofármaco: 18fdg. Dose: Mci.',
    'TC',
    '',
    '{"indicacao": "", "tecnica": "", "achados": "", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Radiofármaco: 18fdg. Dose: Mci.'
    AND ativo = 1
);

-- [055] Glicemia Capilar Pré-dose do Radiofármaco: __mg/dl.
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Glicemia Capilar Pré-dose do Radiofármaco: __mg/dl.',
    'TC',
    'Nota-se acúmulo anômalo do radiofármaco em:
Em comparação ao estudo prévio realizado neste serviço em , observa-se:
Distribuição do radiofármaco de padrão habitual nos demais segmentos corporais estudados.',
    '{"indicacao": "", "tecnica": "", "achados": "Nota-se acúmulo anômalo do radiofármaco em:\\nEm comparação ao estudo prévio realizado neste serviço em , observa-se:\\nDistribuição do radiofármaco de padrão habitual nos demais segmentos corporais estudados.", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Glicemia Capilar Pré-dose do Radiofármaco: __mg/dl.'
    AND ativo = 1
);

-- [056] Como Achados Tomográficos Adicionais Identifica-se:
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Como Achados Tomográficos Adicionais Identifica-se:',
    'TC',
    '',
    '{"indicacao": "", "tecnica": "", "achados": "", "impressao": "PET-CT oncológico evidencia:\\nHipermetabolismo glicolítico em estudo sem evidências de alterações que possam sugerir atividade de neoplasia com avidez pela glicose marcada. Demais achados acima descritos.\\nEm relação ao estudo prévio de , destaca-se:\\nAchados consistentes com", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Como Achados Tomográficos Adicionais Identifica-se:'
    AND ativo = 1
);

-- [057] Critério de Assertividade Diagnóstica*
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Critério de Assertividade Diagnóstica*',
    'TC',
    '',
    '{"indicacao": "", "tecnica": "", "achados": "", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Critério de Assertividade Diagnóstica*'
    AND ativo = 1
);

-- [058] Radiofármaco: 18f - Psma. Dose: Mci.
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Radiofármaco: 18f - Psma. Dose: Mci.',
    'TC',
    'Nota-se acúmulo anômalo do radiofármaco em:',
    '{"indicacao": "", "tecnica": "", "achados": "Nota-se acúmulo anômalo do radiofármaco em:", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Radiofármaco: 18f - Psma. Dose: Mci.'
    AND ativo = 1
);

-- [059] Como Achados Tomográficos Adicionais, Identificam-se:
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Como Achados Tomográficos Adicionais, Identificam-se:',
    'TC',
    '',
    '{"indicacao": "", "tecnica": "", "achados": "", "impressao": "PET-CT COM PSMA-18F demonstra:\\nPSA total e livre (//2020): 4,85 ng/mL; 0,21 ng/mL\\nPET-CT COM PSMA-18F demonstra:\\nFocos de avidez pelo PSMA sugestivas de tecido neoplásico;\\nNão se observam linfonodomegalias ávidas ao PSMA ou não;\\nNão se observam áreas focais de concentração anômala do PSMA no esqueleto.\\nO estudo não evidencia lesões com aumento da expressão de PSMA.\\nNão se observam demais linfonodos ou linfonodomegalias com aumento da expressão de PSMA.\\nEstudo sem evidências de alterações sugestivas de atividade da neoplasia de base.\\nIMPORTANTE: as imagens de CT do estudo PET-CT são usadas para determinar a localização anatômica das lesões de interesse oncológico, não substituindo, portanto, tomografias computadorizadas dirigidas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Como Achados Tomográficos Adicionais, Identificam-se:'
    AND ativo = 1
);

-- [060] Tomografia Computadorizada de Abdome e Pelve
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada de Abdome e Pelve',
    'TC',
    'Exame direcionado para quantificação de gordura visceral e subcutânea.
Gordura visceral = [__] cm²
Gordura subcutânea = [____ ] cm²
Gordura visceral / Gordura visceral + subcutânea = [___]
Circunferência abdominal externa = [___ ] cm',
    '{"indicacao": "", "tecnica": "Imagens obtidas sem a administração intravenosa de meio de contraste iodado.", "achados": "Exame direcionado para quantificação de gordura visceral e subcutânea.\\nGordura visceral = [__] cm²\\nGordura subcutânea = [____ ] cm²\\nGordura visceral / Gordura visceral + subcutânea = [___]\\nCircunferência abdominal externa = [___ ] cm", "impressao": "Exame direcionado para a quantificação de gordura visceral e subcutânea, conforme descrição acima.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada de Abdome e Pelve'
    AND ativo = 1
);

-- [061] Angiotomografia do Abdome Superior e Angiotomografia Arterial e Venosa do Abdome
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia do Abdome Superior e Angiotomografia Arterial e Venosa do Abdome Superior',
    'TC',
    'Fígado com dimensões normais, contornos regulares e atenuação preservada.
Arquitetura vascular hepática preservada.
Vesícula biliar sem anormalidades detectáveis ao método.
Ausência de dilatação das vias biliares.
Pâncreas com dimensões normais e atenuação habitual. Não há dilatação do ducto pancreático principal.
Baço e adrenais sem particularidades.
Rins com dimensões normais, sem cálculos ou hidronefrose.
Aorta com trajeto e calibre preservados.
Não foi caracterizado líquido livre ou linfonodomegalias no abdome superior.
Aorta abdominal pérvia, com trajeto e calibre preservados.
Tronco celíaco, artérias mesentéricas superior e inferior pérvias e com calibre preservado.
Artérias renais únicas, pérvias e com calibre preservado.
Veia cava inferior pérvia, com trajeto e calibre preservados.
Tronco da veia porta e ramos portais principais pérvios e com calibre preservado.
Veias mesentérica superior e esplênica pérvias e com calibre preservado.
Veias renais pérvias e anatômicas.',
    '{"indicacao": "", "tecnica": "Exame realizado com aquisição multislice, com a injeção intravenosa do meio de contraste iodado.", "achados": "Fígado com dimensões normais, contornos regulares e atenuação preservada.\\nArquitetura vascular hepática preservada.\\nVesícula biliar sem anormalidades detectáveis ao método.\\nAusência de dilatação das vias biliares.\\nPâncreas com dimensões normais e atenuação habitual. Não há dilatação do ducto pancreático principal.\\nBaço e adrenais sem particularidades.\\nRins com dimensões normais, sem cálculos ou hidronefrose.\\nAorta com trajeto e calibre preservados.\\nNão foi caracterizado líquido livre ou linfonodomegalias no abdome superior.\\nAorta abdominal pérvia, com trajeto e calibre preservados.\\nTronco celíaco, artérias mesentéricas superior e inferior pérvias e com calibre preservado.\\nArtérias renais únicas, pérvias e com calibre preservado.\\nVeia cava inferior pérvia, com trajeto e calibre preservados.\\nTronco da veia porta e ramos portais principais pérvios e com calibre preservado.\\nVeias mesentérica superior e esplênica pérvias e com calibre preservado.\\nVeias renais pérvias e anatômicas.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia do Abdome Superior e Angiotomografia Arterial e Venosa do Abdome Superior'
    AND ativo = 1
);

-- [062] Angiotomografia do Abdome Superior e Angiotomografia Venosa do Abdome Superior
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Angiotomografia do Abdome Superior e Angiotomografia Venosa do Abdome Superior',
    'TC',
    'Fígado com dimensões normais, contornos regulares e atenuação preservada.
Arquitetura vascular hepática preservada.
Vesícula biliar sem anormalidades detectáveis ao método.
Ausência de dilatação das vias biliares.
Pâncreas com dimensões normais e atenuação habitual. Não há dilatação do ducto pancreático principal.
Baço e adrenais sem particularidades.
Rins com dimensões normais, sem cálculos ou hidronefrose.
Aorta com trajeto e calibre preservados.
Não foi caracterizado líquido livre ou linfonodomegalias no abdome superior.
Veia cava inferior pérvia, com trajeto e calibre preservados.
Tronco da veia porta e ramos portais principais pérvios e com calibre preservado.
Veias mesentérica superior e esplênica pérvias e com calibre preservado.
Veias renais pérvias e anatômicas.',
    '{"indicacao": "", "tecnica": "Exame realizado com aquisição multislice, com a injeção intravenosa do meio de contraste iodado.", "achados": "Fígado com dimensões normais, contornos regulares e atenuação preservada.\\nArquitetura vascular hepática preservada.\\nVesícula biliar sem anormalidades detectáveis ao método.\\nAusência de dilatação das vias biliares.\\nPâncreas com dimensões normais e atenuação habitual. Não há dilatação do ducto pancreático principal.\\nBaço e adrenais sem particularidades.\\nRins com dimensões normais, sem cálculos ou hidronefrose.\\nAorta com trajeto e calibre preservados.\\nNão foi caracterizado líquido livre ou linfonodomegalias no abdome superior.\\nVeia cava inferior pérvia, com trajeto e calibre preservados.\\nTronco da veia porta e ramos portais principais pérvios e com calibre preservado.\\nVeias mesentérica superior e esplênica pérvias e com calibre preservado.\\nVeias renais pérvias e anatômicas.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Angiotomografia do Abdome Superior e Angiotomografia Venosa do Abdome Superior'
    AND ativo = 1
);

-- [063] Tomografia Computadorizada de Tórax e de Abdome Superior
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada de Tórax e de Abdome Superior',
    'TC',
    'Estruturas ósseas sem alterações relevantes.
Espaços pleurais virtuais.
Parênquima pulmonar com atenuação habitual.
Traqueia e brônquios principais pérvios, de calibres normais.
Estruturas vasculares mediastinais com trajeto e calibre conservado.
Ausência de linfonodomegalias mediastinais e hilares.
Fígado de dimensões normais, contornos regulares, com atenuação preservada.
Veia porta pérvia.
<⚠️⚠️> Vesícula biliar sem anormalidades detectáveis ao método.
<⚠️⚠️> Vesícula biliar não identificada (colecistectomia).
<⚠️⚠️> Vesícula biliar não identificada (hipodistendida ou colecistectomia – correlacionar com dados cirúrgicos).
Ausência de dilatação das vias biliares.
Pâncreas de dimensões e contornos regulares. Atenuação do parênquima preservado. Não há evidência de dilatação do ducto pancreático ou de calcificações.
Baço de dimensões normais e textura homogênea.
Rins tópicos de dimensões normais, contornos regulares, com espessura do parênquima sem alterações significativas. Concentração satisfatória do contraste. Não há cálculos ou dilatação do sistema coletor.
Adrenais de dimensões e contornos regulares.
Ausência de linfonodomegalias abdominais ou de líquido livre no abdome superior.
Aorta de trajeto e calibre preservados.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração do meio de contraste endovenoso.", "achados": "Estruturas ósseas sem alterações relevantes.\\nEspaços pleurais virtuais.\\nParênquima pulmonar com atenuação habitual.\\nTraqueia e brônquios principais pérvios, de calibres normais.\\nEstruturas vasculares mediastinais com trajeto e calibre conservado.\\nAusência de linfonodomegalias mediastinais e hilares.\\nFígado de dimensões normais, contornos regulares, com atenuação preservada.\\nVeia porta pérvia.\\n<⚠️⚠️> Vesícula biliar sem anormalidades detectáveis ao método.\\n<⚠️⚠️> Vesícula biliar não identificada (colecistectomia).\\n<⚠️⚠️> Vesícula biliar não identificada (hipodistendida ou colecistectomia – correlacionar com dados cirúrgicos).\\nAusência de dilatação das vias biliares.\\nPâncreas de dimensões e contornos regulares. Atenuação do parênquima preservado. Não há evidência de dilatação do ducto pancreático ou de calcificações.\\nBaço de dimensões normais e textura homogênea.\\nRins tópicos de dimensões normais, contornos regulares, com espessura do parênquima sem alterações significativas. Concentração satisfatória do contraste. Não há cálculos ou dilatação do sistema coletor.\\nAdrenais de dimensões e contornos regulares.\\nAusência de linfonodomegalias abdominais ou de líquido livre no abdome superior.\\nAorta de trajeto e calibre preservados.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada de Tórax e de Abdome Superior'
    AND ativo = 1
);

-- [064] Tomografia Computadorizada do Abdome Superior
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Abdome Superior',
    'TC',
    'Fígado de dimensões normais e contornos regulares. Parênquima hepático com atenuação preservada.
Veia porta pérvia.
<⚠️⚠️> Vesícula biliar sem anormalidades detectáveis ao método.
<⚠️⚠️> Vesícula biliar não identificada (colecistectomia).
<⚠️⚠️> Vesícula biliar não identificada (hipodistendida ou colecistectomia – correlacionar com dados cirúrgicos).
Ausência de dilatação das vias biliares.
Pâncreas com dimensões normais e atenuação habitual. Não há dilatação do ducto pancreático principal.
Baço de dimensões normais e textura homogênea.
Aorta de trajeto e calibre preservados.
Adrenais de dimensões e contornos regulares.
Rins tópicos de dimensões normais, contornos regulares, com espessura do parênquima sem alterações significativas. Concentração satisfatória do contraste. Não há cálculos ou dilatação do sistema coletor.
Ausência de linfonodomegalias ou de líquido livre no abdome superior.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Fígado de dimensões normais e contornos regulares. Parênquima hepático com atenuação preservada.\\nVeia porta pérvia.\\n<⚠️⚠️> Vesícula biliar sem anormalidades detectáveis ao método.\\n<⚠️⚠️> Vesícula biliar não identificada (colecistectomia).\\n<⚠️⚠️> Vesícula biliar não identificada (hipodistendida ou colecistectomia – correlacionar com dados cirúrgicos).\\nAusência de dilatação das vias biliares.\\nPâncreas com dimensões normais e atenuação habitual. Não há dilatação do ducto pancreático principal.\\nBaço de dimensões normais e textura homogênea.\\nAorta de trajeto e calibre preservados.\\nAdrenais de dimensões e contornos regulares.\\nRins tópicos de dimensões normais, contornos regulares, com espessura do parênquima sem alterações significativas. Concentração satisfatória do contraste. Não há cálculos ou dilatação do sistema coletor.\\nAusência de linfonodomegalias ou de líquido livre no abdome superior.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Abdome Superior'
    AND ativo = 1
);

-- [065] Tomografia Computadorizada de Tórax e de Abdome e Pelve
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada de Tórax e de Abdome e Pelve',
    'TC',
    'Estruturas ósseas sem alterações relevantes.
Espaços pleurais virtuais.
Parênquima pulmonar com atenuação habitual.
Traqueia e brônquios principais pérvios, de calibres normais.
Estruturas vasculares mediastinais com trajeto e calibre conservado.
Ausência de linfonodomegalias mediastinais e hilares.
Fígado de dimensões normais, contornos regulares, com atenuação preservada.
Veia porta pérvia.
<⚠️⚠️> Vesícula biliar sem anormalidades detectáveis ao método.
<⚠️⚠️> Vesícula biliar não identificada (colecistectomia).
<⚠️⚠️> Vesícula biliar não identificada (hipodistendida ou colecistectomia – correlacionar com dados cirúrgicos).
Ausência de dilatação das vias biliares.
Pâncreas de dimensões e contornos regulares. Atenuação do parênquima preservado. Não há evidência de dilatação do ducto pancreático ou de calcificações.
Baço de dimensões normais e textura homogênea.
Rins tópicos de dimensões normais, contornos regulares, com espessura do parênquima sem alterações significativas. Concentração satisfatória do contraste. Não há cálculos ou dilatação do sistema coletor.
Adrenais de dimensões e contornos regulares.
Ausência de linfonodomegalias abdominais ou de líquido livre intraperitoneal.
Aorta de trajeto e calibre preservados.
Bexiga com boa repleção, paredes regulares e conteúdo homogêneo.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração do meio de contraste endovenoso.", "achados": "Estruturas ósseas sem alterações relevantes.\\nEspaços pleurais virtuais.\\nParênquima pulmonar com atenuação habitual.\\nTraqueia e brônquios principais pérvios, de calibres normais.\\nEstruturas vasculares mediastinais com trajeto e calibre conservado.\\nAusência de linfonodomegalias mediastinais e hilares.\\nFígado de dimensões normais, contornos regulares, com atenuação preservada.\\nVeia porta pérvia.\\n<⚠️⚠️> Vesícula biliar sem anormalidades detectáveis ao método.\\n<⚠️⚠️> Vesícula biliar não identificada (colecistectomia).\\n<⚠️⚠️> Vesícula biliar não identificada (hipodistendida ou colecistectomia – correlacionar com dados cirúrgicos).\\nAusência de dilatação das vias biliares.\\nPâncreas de dimensões e contornos regulares. Atenuação do parênquima preservado. Não há evidência de dilatação do ducto pancreático ou de calcificações.\\nBaço de dimensões normais e textura homogênea.\\nRins tópicos de dimensões normais, contornos regulares, com espessura do parênquima sem alterações significativas. Concentração satisfatória do contraste. Não há cálculos ou dilatação do sistema coletor.\\nAdrenais de dimensões e contornos regulares.\\nAusência de linfonodomegalias abdominais ou de líquido livre intraperitoneal.\\nAorta de trajeto e calibre preservados.\\nBexiga com boa repleção, paredes regulares e conteúdo homogêneo.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada de Tórax e de Abdome e Pelve'
    AND ativo = 1
);

-- [066] Tomografia Computadorizada do Abdome e Pelve com Ênfase na Avaliação da Parede A
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Abdome e Pelve com Ênfase na Avaliação da Parede Abdominal',
    'TC',
    'Pele e tecido subcutâneo sem particularidades.
Aponeurose dos retos abdominais com continuidade conservada. Distância entre os bordos mediais dos ventres musculares do reto abdominal de até ___ cm.
Cicatriz umbilical de aspecto habitual, sem herniações em repouso ou à manobra de Valsalva.
Linhas semilunares conservadas, sem descontinuidades.
Planos musculares anatômicos, sem evidências de lesões ou atrofia.
Não se observam áreas de descontinuidade da parede evidenciáveis pelo método.
Ausência de coleções, formações expansivas ou de linfonodomegalias.
<⚠️⚠️> Exemplo de descrição: Hérnia da parede abdominal anterior na região [epigástrica/hipogástrica/umbilical/mesogástrica] [mediana/paramediana] com colo de ___ x ___ cm, notando-se insinuação de [conteúdo adiposo / alças intestinais], sem/com sinais obstrutivos ou de sofrimento.
<⚠️⚠️> Volumetria em caso de hérnia incisional volumosa ou solicitação médica:
Volume do saco herniário: [calcular com os diâmetros máximos do saco herniário LL x CC x AP x 0,52] cm³
Volume da cavidade abdominal: [calcular com os diâmetros máximos da cavidade abdominal LL x CC x AP x 0,52] cm³
Volume peritoneal total (saco herniário + cavidade abdominal): [soma dos volumes calculados acima] ___ cm³
Relação (saco herniário / cavidade abdominal): [divisão do volume do saco herminário pelo volume da cavidade x 100] ___ %
Demais achados:
Fígado, baço, pâncreas, adrenais, rins e bexiga com morfologia e atenuação preservadas.
Ausência de dilatação das vias biliares.',
    '{"indicacao": "", "tecnica": "Imagens adquiridas com a administração endovenosa do meio de contraste iodado. \\n<⚠️⚠️> Exame realizado em repouso e durante a manobra de Valsalva.", "achados": "Pele e tecido subcutâneo sem particularidades.\\nAponeurose dos retos abdominais com continuidade conservada. Distância entre os bordos mediais dos ventres musculares do reto abdominal de até ___ cm.\\nCicatriz umbilical de aspecto habitual, sem herniações em repouso ou à manobra de Valsalva.\\nLinhas semilunares conservadas, sem descontinuidades.\\nPlanos musculares anatômicos, sem evidências de lesões ou atrofia.\\nNão se observam áreas de descontinuidade da parede evidenciáveis pelo método.\\nAusência de coleções, formações expansivas ou de linfonodomegalias.\\n<⚠️⚠️> Exemplo de descrição: Hérnia da parede abdominal anterior na região [epigástrica/hipogástrica/umbilical/mesogástrica] [mediana/paramediana] com colo de ___ x ___ cm, notando-se insinuação de [conteúdo adiposo / alças intestinais], sem/com sinais obstrutivos ou de sofrimento.\\n<⚠️⚠️> Volumetria em caso de hérnia incisional volumosa ou solicitação médica:\\nVolume do saco herniário: [calcular com os diâmetros máximos do saco herniário LL x CC x AP x 0,52] cm³\\nVolume da cavidade abdominal: [calcular com os diâmetros máximos da cavidade abdominal LL x CC x AP x 0,52] cm³\\nVolume peritoneal total (saco herniário + cavidade abdominal): [soma dos volumes calculados acima] ___ cm³\\nRelação (saco herniário / cavidade abdominal): [divisão do volume do saco herminário pelo volume da cavidade x 100] ___ %\\nDemais achados:\\nFígado, baço, pâncreas, adrenais, rins e bexiga com morfologia e atenuação preservadas.\\nAusência de dilatação das vias biliares.", "impressao": "Exame dentro dos padrões da normalidade.\\n¹ Claus C M P, et al. Relatório DECOMP: Respostas que os cirurgiões esperam de um exame de imagem da parede abdominal. Rev Col Bras Cir 49:e20223172. 2021. doi: 10.1590/0100-6991e-20223172", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Abdome e Pelve com Ênfase na Avaliação da Parede Abdominal'
    AND ativo = 1
);

-- [067] Tomografia Computadorizada de Abdome Total (abdome Superior e Pelve)
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada de Abdome Total (abdome Superior e Pelve)',
    'TC',
    'Rins tópicos, de dimensões normais e contornos regulares, apresentando boa concentração e eliminação do meio de contraste iodado.
Rim direito mede:  cm. Vol:  cm³.
Rim esquerdo mede:  cm. Vol:  cm³.
** Cistos corticais de aspecto simples bilaterais medindo até xx cm.
** Cisto cortical simples no terço superior/médio/inferior medindo xx cm.
Sistema coletor:
Sistema coletor anatômico.
Ausência de cálculos ou hidronefrose.
Rim direito:
Rim esquerdo:
** Cálculos: xx cm no grupamento calicinal xx.
Sistema arterial:
Artérias renais pérvias e de calibre normal.
Rim direito:
Rim esquerdo:
Artéria renal (direita/esquerda) única.
** Presença de artéria acessória (polar/hilar) superior que se origina da aorta xx cm acima da artéria renal principal.
** Presença de artéria acessória (polar/hilar) inferior que se origina da aorta xx cm abaixo da artéria renal principal.
** Bifurcação precoce da artéria renal xx cm após sua emergência na aorta, originando artéria polar superior/inferior.
** Presença de duas artérias renais que se originam da aorta a uma distância de xx cm entre elas.
** Leve compressão extrínseca da emergência da artéria renal direita pelo pilar diafragmático direito, sem determinar estenose significativa.
Sistema venoso:
Veias renais pérvias e anatômicas.
VRD mede:  cm
VRE mede:  cm
** Veia adrenal desemboca na veia renal esquerda à --- cm do hilo renal.
** Presença de veia lombar drenando na veia renal esquerda a --- do hilo renal.
** Veia renal esquerda circunaórtica.
** Veia renal esquerda retroaórtica.
** Presença de duas veias renais que drenam na VCI separadamente sendo a mais superior/inferior a mais calibrosa.
** Leve compressão da veia renal esquerda entre a aorta e a artéria mesentérica superior, determinando ectasia da veia e pequenas colaterais a montante. A valorização deste achado como Síndrome de Nutcracker depende da correlação clínico-laboratorial.',
    '{"indicacao": "", "tecnica": "Imagens obtidas antes e após a administração intravenosa do meio de contraste iodado. Exame direcionado para a avaliação do sistema urinário.", "achados": "Rins tópicos, de dimensões normais e contornos regulares, apresentando boa concentração e eliminação do meio de contraste iodado.\\nRim direito mede:  cm. Vol:  cm³.\\nRim esquerdo mede:  cm. Vol:  cm³.\\n** Cistos corticais de aspecto simples bilaterais medindo até xx cm.\\n** Cisto cortical simples no terço superior/médio/inferior medindo xx cm.\\nSistema coletor:\\nSistema coletor anatômico.\\nAusência de cálculos ou hidronefrose.\\nRim direito:\\nRim esquerdo:\\n** Cálculos: xx cm no grupamento calicinal xx.\\nSistema arterial:\\nArtérias renais pérvias e de calibre normal.\\nRim direito:\\nRim esquerdo:\\nArtéria renal (direita/esquerda) única.\\n** Presença de artéria acessória (polar/hilar) superior que se origina da aorta xx cm acima da artéria renal principal.\\n** Presença de artéria acessória (polar/hilar) inferior que se origina da aorta xx cm abaixo da artéria renal principal.\\n** Bifurcação precoce da artéria renal xx cm após sua emergência na aorta, originando artéria polar superior/inferior.\\n** Presença de duas artérias renais que se originam da aorta a uma distância de xx cm entre elas.\\n** Leve compressão extrínseca da emergência da artéria renal direita pelo pilar diafragmático direito, sem determinar estenose significativa.\\nSistema venoso:\\nVeias renais pérvias e anatômicas.\\nVRD mede:  cm\\nVRE mede:  cm\\n** Veia adrenal desemboca na veia renal esquerda à --- cm do hilo renal.\\n** Presença de veia lombar drenando na veia renal esquerda a --- do hilo renal.\\n** Veia renal esquerda circunaórtica.\\n** Veia renal esquerda retroaórtica.\\n** Presença de duas veias renais que drenam na VCI separadamente sendo a mais superior/inferior a mais calibrosa.\\n** Leve compressão da veia renal esquerda entre a aorta e a artéria mesentérica superior, determinando ectasia da veia e pequenas colaterais a montante. A valorização deste achado como Síndrome de Nutcracker depende da correlação clínico-laboratorial.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada de Abdome Total (abdome Superior e Pelve)'
    AND ativo = 1
);

-- [068] Tomografia Computadorizada do Antebraço Direito
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Antebraço Direito',
    'TC',
    'Estruturas ósseas de forma conservada, sem sinais de fraturas.
Espaços articulares de contornos regulares.
Não há sinais de derrame articular.
Planos musculares sem alterações tomográficas.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa de contraste iodado.", "achados": "Estruturas ósseas de forma conservada, sem sinais de fraturas.\\nEspaços articulares de contornos regulares.\\nNão há sinais de derrame articular.\\nPlanos musculares sem alterações tomográficas.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Antebraço Direito'
    AND ativo = 1
);

-- [069] Tomografia Computadorizada dos Antebraços (direito e Esquerdo)
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada dos Antebraços (direito e Esquerdo)',
    'TC',
    'Antebraço DIREITO:
Estruturas ósseas de forma conservada, sem sinais de fraturas.
Espaços articulares de contornos regulares.
Não há sinais de derrame articular.
Planos musculares sem alterações tomográficas.
Antebraço ESQUERDO:
Estruturas ósseas de forma conservada, sem sinais de fraturas.
Espaços articulares de contornos regulares.
Não há sinais de derrame articular.
Planos musculares sem alterações tomográficas.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa de contraste iodado.", "achados": "Antebraço DIREITO:\\nEstruturas ósseas de forma conservada, sem sinais de fraturas.\\nEspaços articulares de contornos regulares.\\nNão há sinais de derrame articular.\\nPlanos musculares sem alterações tomográficas.\\nAntebraço ESQUERDO:\\nEstruturas ósseas de forma conservada, sem sinais de fraturas.\\nEspaços articulares de contornos regulares.\\nNão há sinais de derrame articular.\\nPlanos musculares sem alterações tomográficas.", "impressao": "Antebraço DIREITO:\\n- Estruturas avaliadas de aspecto preservado.\\nAntebraço ESQUERDO:\\n- Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada dos Antebraços (direito e Esquerdo)'
    AND ativo = 1
);

-- [070] Tomografia Computadorizada do Antepé Direito
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Antepé Direito',
    'TC',
    'Estruturas ósseas de formato habitual, sem sinais de fraturas.
Espaços articulares de contornos regulares.
Não foi identificado derrame articular significativo.
Planos musculares e tendíneos sem alterações tomográficas.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa do meio de contraste.", "achados": "Estruturas ósseas de formato habitual, sem sinais de fraturas.\\nEspaços articulares de contornos regulares.\\nNão foi identificado derrame articular significativo.\\nPlanos musculares e tendíneos sem alterações tomográficas.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Antepé Direito'
    AND ativo = 1
);

-- [071] Tomografia Computadorizada dos Antepés (direito e Esquerdo)
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada dos Antepés (direito e Esquerdo)',
    'TC',
    'Imagens obtidas com a administração endovenosa do meio de contraste.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa do meio de contraste.", "achados": "", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada dos Antepés (direito e Esquerdo)'
    AND ativo = 1
);

-- [072] Antepé Direito:
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Antepé Direito:',
    'TC',
    '',
    '{"indicacao": "", "tecnica": "", "achados": "", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Antepé Direito:'
    AND ativo = 1
);

-- [073] Antepé Esquerdo:
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Antepé Esquerdo:',
    'TC',
    '',
    '{"indicacao": "", "tecnica": "", "achados": "", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Antepé Esquerdo:'
    AND ativo = 1
);

-- [074] Tomografia Computadorizada dos Arcos Costais
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada dos Arcos Costais',
    'TC',
    'Arcos costais com morfologia e textura conservadas.
Não há sinais de fraturas ou lesões ósseas focais com características agressivas.
Articulações esternoclaviculares, esternocostais e costovertebrais congruentes e regulares.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa de contraste iodado.", "achados": "Arcos costais com morfologia e textura conservadas.\\nNão há sinais de fraturas ou lesões ósseas focais com características agressivas.\\nArticulações esternoclaviculares, esternocostais e costovertebrais congruentes e regulares.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada dos Arcos Costais'
    AND ativo = 1
);

-- [075] Tomografia Computadorizada da Articulação Esternoclavicular Direita
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada da Articulação Esternoclavicular Direita',
    'TC',
    'Não há sinais de fraturas ou lesões ósseas focais com características agressivas.
Articulações esternoclavicular e esternocostais homolaterais congruentes e regulares.
Demais estruturas ósseas de aspecto preservado.
Planos musculares com trofismo preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa de contraste iodado.", "achados": "Não há sinais de fraturas ou lesões ósseas focais com características agressivas.\\nArticulações esternoclavicular e esternocostais homolaterais congruentes e regulares.\\nDemais estruturas ósseas de aspecto preservado.\\nPlanos musculares com trofismo preservado.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada da Articulação Esternoclavicular Direita'
    AND ativo = 1
);

-- [076] Tomografia Computadorizada das Articulações Temporomandibulares Direita e Esquer
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada das Articulações Temporomandibulares Direita e Esquerda',
    'TC',
    'ATM direita:
Cavidade articular de contornos regulares.
Côndilo mandibular de morfologia preservada, com excursão adequada à abertura bucal. 
Não há evidências de derrame articular significativo.
ATM esquerda:
Cavidade articular de contornos regulares.
Côndilo mandibular de morfologia preservada, com excursão adequada à abertura bucal. 
Não há evidências de derrame articular significativo.
<⚠️EXCURSÃO REDUZIDA⚠️> Excursão reduzida do côndilo mandibular à abertura bucal. 
<⚠️HIPERMOBILIDADE⚠️> Excursão aumentada do côndilo mandibular à abertura bucal, ultrapassando o ápice da eminência temporal.
<⚠️LEVE ARTROPATIA DEGENERATIVA⚠️> Discretas alterações degenerativas da ATM, caracterizadas leve retificação do côndilo mandibular e diminutos osteófitos marginais. 
<⚠️ARTROPATIA DEGENERATIVA⚠️> Alterações degenerativas da ATM, caracterizadas por retificação e irregularidades da superfície articular do côndilo mandibular, e pequenos osteófitos marginais.
<⚠️ARTROSE AVANÇADA⚠️> Artrose avançada da ATM, caracterizada por osteófitos marginais, redução do espaço articular, esclerose subcondral, retificação e irregularidades do côndilo mandibular. 
<⚠️DERRAME ARTICULAR⚠️> Derrame articular na ATM.
<⚠️ARTRITE⚠️> Derrame articular, sinovite e erosões ósseas na ATM, sugerindo provável artropatia inflamatória.',
    '{"indicacao": "", "tecnica": "Imagens obtidas antes e após a administração endovenosa do meio de contraste.", "achados": "ATM direita:\\nCavidade articular de contornos regulares.\\nCôndilo mandibular de morfologia preservada, com excursão adequada à abertura bucal. \\nNão há evidências de derrame articular significativo.\\nATM esquerda:\\nCavidade articular de contornos regulares.\\nCôndilo mandibular de morfologia preservada, com excursão adequada à abertura bucal. \\nNão há evidências de derrame articular significativo.\\n<⚠️EXCURSÃO REDUZIDA⚠️> Excursão reduzida do côndilo mandibular à abertura bucal. \\n<⚠️HIPERMOBILIDADE⚠️> Excursão aumentada do côndilo mandibular à abertura bucal, ultrapassando o ápice da eminência temporal.\\n<⚠️LEVE ARTROPATIA DEGENERATIVA⚠️> Discretas alterações degenerativas da ATM, caracterizadas leve retificação do côndilo mandibular e diminutos osteófitos marginais. \\n<⚠️ARTROPATIA DEGENERATIVA⚠️> Alterações degenerativas da ATM, caracterizadas por retificação e irregularidades da superfície articular do côndilo mandibular, e pequenos osteófitos marginais.\\n<⚠️ARTROSE AVANÇADA⚠️> Artrose avançada da ATM, caracterizada por osteófitos marginais, redução do espaço articular, esclerose subcondral, retificação e irregularidades do côndilo mandibular. \\n<⚠️DERRAME ARTICULAR⚠️> Derrame articular na ATM.\\n<⚠️ARTRITE⚠️> Derrame articular, sinovite e erosões ósseas na ATM, sugerindo provável artropatia inflamatória.", "impressao": "ATM direita:\\nExame dentro dos padrões da normalidade.\\nATM esquerda:\\nExame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada das Articulações Temporomandibulares Direita e Esquerda'
    AND ativo = 1
);

-- [077] Tomografia Computadorizada das Articulações Esternoclaviculares
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada das Articulações Esternoclaviculares',
    'TC',
    'Não há sinais de fraturas ou lesões ósseas focais com características agressivas.
Articulações esternoclaviculares e esternocostais congruentes e regulares.
Demais estruturas ósseas de aspecto preservado.
Planos musculares com trofismo preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa de contraste iodado.", "achados": "Não há sinais de fraturas ou lesões ósseas focais com características agressivas.\\nArticulações esternoclaviculares e esternocostais congruentes e regulares.\\nDemais estruturas ósseas de aspecto preservado.\\nPlanos musculares com trofismo preservado.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada das Articulações Esternoclaviculares'
    AND ativo = 1
);

-- [078] Tomografia Computadorizada da Bacia
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada da Bacia',
    'TC',
    'Articulações sacroilíacas congruentes e com contornos regulares. Não se observam erosões corticais.
Sínfise púbica de contornos regulares.
Articulações femoroacetabulares de contornos regulares.
Ausência de derrame articular significativo.
Estruturas ósseas de forma conservada, sem evidência de fraturas.
Planos musculares e tendíneos sem alterações tomográficas.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa de contraste iodado.", "achados": "Articulações sacroilíacas congruentes e com contornos regulares. Não se observam erosões corticais.\\nSínfise púbica de contornos regulares.\\nArticulações femoroacetabulares de contornos regulares.\\nAusência de derrame articular significativo.\\nEstruturas ósseas de forma conservada, sem evidência de fraturas.\\nPlanos musculares e tendíneos sem alterações tomográficas.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada da Bacia'
    AND ativo = 1
);

-- [079] Tomografia Computadorizada de Base do Crânio
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada de Base do Crânio',
    'TC',
    'Estruturas ósseas de aspecto habitual, sem sinais de fratura.
Seio cavernoso com atenuação habitual e amplitude preservada.
Forames da base com amplitude preservada, sem evidência de formação expansiva ao método.
#### Não se observam realces anômalos.',
    '{"indicacao": "", "tecnica": "Imagens obtidas antes e após a administração endovenosa do meio de contraste.", "achados": "Estruturas ósseas de aspecto habitual, sem sinais de fratura.\\nSeio cavernoso com atenuação habitual e amplitude preservada.\\nForames da base com amplitude preservada, sem evidência de formação expansiva ao método.\\n#### Não se observam realces anômalos.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada de Base do Crânio'
    AND ativo = 1
);

-- [080] Tomografia Computadorizada do Braço Direito
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Braço Direito',
    'TC',
    'Estruturas ósseas de forma conservada, sem sinais de fraturas.
Espaços articulares de contornos regulares.
Não há sinais de derrame articular.
Planos musculares sem alterações tomográficas.',
    '{"indicacao": "", "tecnica": "Imagens obtidas sem a administração endovenosa de contraste iodado.", "achados": "Estruturas ósseas de forma conservada, sem sinais de fraturas.\\nEspaços articulares de contornos regulares.\\nNão há sinais de derrame articular.\\nPlanos musculares sem alterações tomográficas.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Braço Direito'
    AND ativo = 1
);

-- [081] Tomografia Computadorizada do Calcâneo Direito
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Calcâneo Direito',
    'TC',
    'Estruturas ósseas de formato habitual, sem sinais de fraturas.
Espaços articulares de contornos regulares.
Não há sinais de derrame articular.
Planos musculares e tendíneos sem alterações tomográficas.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa de contraste iodado.", "achados": "Estruturas ósseas de formato habitual, sem sinais de fraturas.\\nEspaços articulares de contornos regulares.\\nNão há sinais de derrame articular.\\nPlanos musculares e tendíneos sem alterações tomográficas.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Calcâneo Direito'
    AND ativo = 1
);

-- [082] Tomografia Computadorizada dos Calcâneos (direito e Esquerdo)
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada dos Calcâneos (direito e Esquerdo)',
    'TC',
    'Calcâneo DIREITO:
Estruturas ósseas de formato habitual, sem sinais de fraturas.
Espaços articulares de contornos regulares.
Não foi identificado derrame articular significativo.
Planos musculares e tendíneos sem alterações tomográficas.
Calcâneo ESQUERDO:
Estruturas ósseas de formato habitual, sem sinais de fraturas.
Espaços articulares de contornos regulares.
Não foi identificado derrame articular significativo.
Planos musculares e tendíneos sem alterações tomográficas.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa de contraste iodado.", "achados": "Calcâneo DIREITO:\\nEstruturas ósseas de formato habitual, sem sinais de fraturas.\\nEspaços articulares de contornos regulares.\\nNão foi identificado derrame articular significativo.\\nPlanos musculares e tendíneos sem alterações tomográficas.\\nCalcâneo ESQUERDO:\\nEstruturas ósseas de formato habitual, sem sinais de fraturas.\\nEspaços articulares de contornos regulares.\\nNão foi identificado derrame articular significativo.\\nPlanos musculares e tendíneos sem alterações tomográficas.", "impressao": "Calcâneo DIREITO:\\n- Estruturas avaliadas de aspecto preservado.\\nCalcâneo ESQUERDO:\\n- Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada dos Calcâneos (direito e Esquerdo)'
    AND ativo = 1
);

-- [083] Tomografia Computadorizada da Clavícula Esquerda
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada da Clavícula Esquerda',
    'TC',
    'Estruturas ósseas de formato habitual, sem sinais de fraturas ou lesões ósseas focais com características agressivas.
Articulações esternoclavicular e acromioclavicular congruentes e regulares.
Não há sinais de derrame articular.
Planos musculares apresentam trofismo preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas sem a administração endovenosa de contraste iodado.", "achados": "Estruturas ósseas de formato habitual, sem sinais de fraturas ou lesões ósseas focais com características agressivas.\\nArticulações esternoclavicular e acromioclavicular congruentes e regulares.\\nNão há sinais de derrame articular.\\nPlanos musculares apresentam trofismo preservado.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada da Clavícula Esquerda'
    AND ativo = 1
);

-- [084] Tomografia Computadorizada da Coluna Cervical e Lombar
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada da Coluna Cervical e Lombar',
    'TC',
    'Processo odontoide centrado e com contornos regulares.
Corpos vertebrais alinhados e com alturas preservadas.
Discos intervertebrais de altura preservada.
Nível C2-C3: ausência de abaulamentos ou protrusões discais significativos.
Nível C3-C4:
Nível C4-C5:
Nível C5-C6:
Nível C6-C7:
Nível C7-T1:
Nível D12-L1:
Nível L1-L2:
Nível L2-L3:
Nível L3-L4:
Nível L4-L5:
Nível L5-S1:
Articulações interapofisárias de contornos regulares.
Canal vertebral e forames neurais sem estenoses significativas.
Musculatura paravertebral apresenta trofismo preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa de contraste iodado.", "achados": "Processo odontoide centrado e com contornos regulares.\\nCorpos vertebrais alinhados e com alturas preservadas.\\nDiscos intervertebrais de altura preservada.\\nNível C2-C3: ausência de abaulamentos ou protrusões discais significativos.\\nNível C3-C4:\\nNível C4-C5:\\nNível C5-C6:\\nNível C6-C7:\\nNível C7-T1:\\nNível D12-L1:\\nNível L1-L2:\\nNível L2-L3:\\nNível L3-L4:\\nNível L4-L5:\\nNível L5-S1:\\nArticulações interapofisárias de contornos regulares.\\nCanal vertebral e forames neurais sem estenoses significativas.\\nMusculatura paravertebral apresenta trofismo preservado.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada da Coluna Cervical e Lombar'
    AND ativo = 1
);

-- [085] Tomografia Computadorizada da Coluna Cervical e Lombossacra
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada da Coluna Cervical e Lombossacra',
    'TC',
    'Processo odontoide centrado e com contornos regulares.
Corpos vertebrais alinhados e com alturas preservadas.
Discos intervertebrais de altura preservada, sem abaulamentos ou protrusões significativas.
Nível C2-C3: ausência de abaulamentos ou protrusões discais significativas.
Nível C3-C4:
Nível C4-C5:
Nível C5-C6:
Nível C6-C7:
Nível C7-T1:
Nível D12-L1:
Nível L1-L2:
Nível L2-L3:
Nível L3-L4:
Nível L4-L5:
Nível L5-S1:
Articulações interapofisárias de contornos regulares.
Canal vertebral e forames neurais sem estenoses significativas.
Musculatura paravertebral apresenta trofismo preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas sem a administração endovenosa de contraste iodado.", "achados": "Processo odontoide centrado e com contornos regulares.\\nCorpos vertebrais alinhados e com alturas preservadas.\\nDiscos intervertebrais de altura preservada, sem abaulamentos ou protrusões significativas.\\nNível C2-C3: ausência de abaulamentos ou protrusões discais significativas.\\nNível C3-C4:\\nNível C4-C5:\\nNível C5-C6:\\nNível C6-C7:\\nNível C7-T1:\\nNível D12-L1:\\nNível L1-L2:\\nNível L2-L3:\\nNível L3-L4:\\nNível L4-L5:\\nNível L5-S1:\\nArticulações interapofisárias de contornos regulares.\\nCanal vertebral e forames neurais sem estenoses significativas.\\nMusculatura paravertebral apresenta trofismo preservado.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada da Coluna Cervical e Lombossacra'
    AND ativo = 1
);

-- [086] Tomografia Computadorizada da Coluna Cervical
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada da Coluna Cervical',
    'TC',
    'Processo odontoide centrado e com contornos regulares.
Corpos vertebrais alinhados e com alturas preservadas.
Discos intervertebrais de altura preservada.
Nível C2-C3: ausência de abaulamentos ou de protrusões discais significativos.
Nível C3-C4:
Nível C4-C5:
Nível C5-C6:
Nível C6-C7:
Nível C7-T1:
Articulações interfacetárias e uncovertebrais de contornos regulares.
Canal vertebral e forames neurais sem estenoses significativas.
Musculatura paravertebral apresenta trofismo preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas sem a administração endovenosa de contraste iodado.", "achados": "Processo odontoide centrado e com contornos regulares.\\nCorpos vertebrais alinhados e com alturas preservadas.\\nDiscos intervertebrais de altura preservada.\\nNível C2-C3: ausência de abaulamentos ou de protrusões discais significativos.\\nNível C3-C4:\\nNível C4-C5:\\nNível C5-C6:\\nNível C6-C7:\\nNível C7-T1:\\nArticulações interfacetárias e uncovertebrais de contornos regulares.\\nCanal vertebral e forames neurais sem estenoses significativas.\\nMusculatura paravertebral apresenta trofismo preservado.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada da Coluna Cervical'
    AND ativo = 1
);

-- [087] Tomografia Computadorizada do Corpo Inteiro para Rastreamento de Lesões Ósseas d
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Corpo Inteiro para Rastreamento de Lesões Ósseas de Mieloma Múltiplo',
    'TC',
    'Exame direcionado para rastreamento de lesões de mieloma múltiplo, com aquisição de imagens sem a administração endovenosa do meio de contraste endovenoso.
Achados de maior relevância oncológica:
Rastreamento negativo para acometimento ósseo pelo mieloma múltiplo.
Não foram identificadas lesões suspeitas para acometimento ósseo pelo mieloma múltiplo.
XXXXXX Lesões  líticas suspeitas para acometimento ósseo pelo mieloma múltiplo nas seguintes localizações:
- Lesões em múltiplas vértebras cervicais, torácicas e lombares, com fraturas patológicas em diversos níveis, sem estenose significativa do canal vertebral;
- Lesões em múltiplas vértebras cervicais, torácicas e lombares, com fraturas patológicas em diversos níveis e componentes de partes moles extraósseos, determinando aparente estenose do canal vertebral nos níveis xxx e de diversos forames neurais. À critério clínico, complementar com RM da coluna.
XXX Rastreamento positivo para mieloma múltiplo, com padrão de acometimento micronodular difuso, caracterizado por heterogeneidade e rarefação difusa do trabeculado ósseo, com múltiplas pequenas lesões líticas esparsas pelo arcabouço ósseo, mais evidentes na calota craniana, nos arcos costais e na bacia. Não há evidências de rotura cortical ou componente tumoral extraósseo, todavia se observam lesões com recorte endosteal, principalmente na calota craniana e em arcos costais.
XXXXX  Pequenos focos radiolucentes no xxx, de aspecto indeterminado às imagens obtidas.
XXXXX Lesões focais no corpo de L2, asa sacral bilateral e no ilíaco esquerdo, de provável natureza benigna.
Demais achados:
XXXXX
Opinião:
Rastreamento negativo para acometimento ósseo pelo mieloma múltiplo.
XXXX Rastreamento positivo para acometimento ósseo pelo mieloma múltiplo.',
    '{"indicacao": "", "tecnica": "Exame direcionado para rastreamento de lesões de mieloma múltiplo, com aquisição de imagens sem a administração endovenosa do meio de contraste endovenoso.\\nAchados de maior relevância oncológica:\\nRastreamento negativo para acometimento ósseo pelo mieloma múltiplo.\\nNão foram identificadas lesões suspeitas para acometimento ósseo pelo mieloma múltiplo.\\nXXXXXX Lesões  líticas suspeitas para acometimento ósseo pelo mieloma múltiplo nas seguintes localizações:\\n- Lesões em múltiplas vértebras cervicais, torácicas e lombares, com fraturas patológicas em diversos níveis, sem estenose significativa do canal vertebral;\\n- Lesões em múltiplas vértebras cervicais, torácicas e lombares, com fraturas patológicas em diversos níveis e componentes de partes moles extraósseos, determinando aparente estenose do canal vertebral nos níveis xxx e de diversos forames neurais. À critério clínico, complementar com RM da coluna.\\nXXX Rastreamento positivo para mieloma múltiplo, com padrão de acometimento micronodular difuso, caracterizado por heterogeneidade e rarefação difusa do trabeculado ósseo, com múltiplas pequenas lesões líticas esparsas pelo arcabouço ósseo, mais evidentes na calota craniana, nos arcos costais e na bacia. Não há evidências de rotura cortical ou componente tumoral extraósseo, todavia se observam lesões com recorte endosteal, principalmente na calota craniana e em arcos costais.\\nXXXXX  Pequenos focos radiolucentes no xxx, de aspecto indeterminado às imagens obtidas.\\nXXXXX Lesões focais no corpo de L2, asa sacral bilateral e no ilíaco esquerdo, de provável natureza benigna.\\nDemais achados:\\nXXXXX\\nOpinião:\\nRastreamento negativo para acometimento ósseo pelo mieloma múltiplo.\\nXXXX Rastreamento positivo para acometimento ósseo pelo mieloma múltiplo.", "achados": "", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Corpo Inteiro para Rastreamento de Lesões Ósseas de Mieloma Múltiplo'
    AND ativo = 1
);

-- [088] Tomografia Computadorizada do Cotovelo Direito
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Cotovelo Direito',
    'TC',
    'Estruturas ósseas de forma conservada, sem sinais de fraturas.
Espaços articulares de contornos regulares.
Não há sinais de derrame articular significativo.
Planos musculares com trofismo preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa de contraste iodado.", "achados": "Estruturas ósseas de forma conservada, sem sinais de fraturas.\\nEspaços articulares de contornos regulares.\\nNão há sinais de derrame articular significativo.\\nPlanos musculares com trofismo preservado.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Cotovelo Direito'
    AND ativo = 1
);

-- [089] Tomografia Computadorizada da Coxa Direita
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada da Coxa Direita',
    'TC',
    'Estruturas ósseas de forma conservada, sem sinais de fraturas.
Espaços articulares de contornos regulares.
Não há sinais de derrame articular significativo.
Planos musculares com trofismo preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas sem a administração endovenosa de contraste iodado.", "achados": "Estruturas ósseas de forma conservada, sem sinais de fraturas.\\nEspaços articulares de contornos regulares.\\nNão há sinais de derrame articular significativo.\\nPlanos musculares com trofismo preservado.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada da Coxa Direita'
    AND ativo = 1
);

-- [090] Tomografia Computadorizada do Crânio e Angiotomografia Arterial e Venosa Intracr
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Crânio e Angiotomografia Arterial e Venosa Intracraniana e Cervical',
    'TC',
    'Sistema ventricular de morfologia e dimensões preservadas.
Não há desvio de estruturas da linha mediana.
Cisternas e sulcos corticais de amplitude dentro dos limites normais.
Parênquima encefálico com atenuação habitual.
Ausência de coleções extra-axiais nos cortes obtidos.
Angio ARTERIAL:
Segmentos intracranianos das artérias carótidas internas, artérias cerebrais anteriores, médias e posteriores de calibre, trajeto e contornos preservados.
Segmento intracraniano das artérias vertebrais e artéria basilar de calibre, trajeto e contornos preservados
Demais artérias intracranianas detectadas por esta técnica apresentando calibre, trajeto e contornos preservados.
Ausência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.
Angio VENOSA:
Seio sagital superior, veia de Galeno, veias cerebrais internas, seio reto, seios transversos e sigmóides, e porção cranial das veias jugulares internas de calibre, trajeto e fluxo preservados.
Não foram observados sinais de circulação patológica.
XXX Assimetria do calibre e fluxo dos seios transversos, sendo menor à XX, mais comumente significativo de hipoplasia deste lado.
XXX Seio sagital superior, seio reto, veia cerebral magna e cerebrais internas com fluxo preservado.
XXX Imagens arredondadas junto ao seio XX com atenuação semelhante ao líquor, compatíveis com granulações aracnoídeas.
Bulbos jugulares e porções proximais das veias jugulares internas pérvios.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa de contraste iodado.", "achados": "Sistema ventricular de morfologia e dimensões preservadas.\\nNão há desvio de estruturas da linha mediana.\\nCisternas e sulcos corticais de amplitude dentro dos limites normais.\\nParênquima encefálico com atenuação habitual.\\nAusência de coleções extra-axiais nos cortes obtidos.\\nAngio ARTERIAL:\\nSegmentos intracranianos das artérias carótidas internas, artérias cerebrais anteriores, médias e posteriores de calibre, trajeto e contornos preservados.\\nSegmento intracraniano das artérias vertebrais e artéria basilar de calibre, trajeto e contornos preservados\\nDemais artérias intracranianas detectadas por esta técnica apresentando calibre, trajeto e contornos preservados.\\nAusência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.\\nAngio VENOSA:\\nSeio sagital superior, veia de Galeno, veias cerebrais internas, seio reto, seios transversos e sigmóides, e porção cranial das veias jugulares internas de calibre, trajeto e fluxo preservados.\\nNão foram observados sinais de circulação patológica.\\nXXX Assimetria do calibre e fluxo dos seios transversos, sendo menor à XX, mais comumente significativo de hipoplasia deste lado.\\nXXX Seio sagital superior, seio reto, veia cerebral magna e cerebrais internas com fluxo preservado.\\nXXX Imagens arredondadas junto ao seio XX com atenuação semelhante ao líquor, compatíveis com granulações aracnoídeas.\\nBulbos jugulares e porções proximais das veias jugulares internas pérvios.", "impressao": "Estruturas avaliadas de aspecto preservado.\\nNão há evidências de estenoses ou aneurismas.\\nNão há sinais de trombose venosa recente.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Crânio e Angiotomografia Arterial e Venosa Intracraniana e Cervical'
    AND ativo = 1
);

-- [091] Tomografia Computadorizada do Crânio e Angiotomografia Arterial Intracraniana
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Crânio e Angiotomografia Arterial Intracraniana',
    'TC',
    'Crânio:
Sistema ventricular de morfologia e dimensões preservadas.
Não há desvio de estruturas da linha mediana.
Cisternas e sulcos corticais de amplitude dentro dos limites normais.
Parênquima encefálico com coeficientes de atenuação habituais.
Ausência de coleções extra-axiais nos cortes obtidos.
Angio Arterial:
Segmentos intracranianos das artérias carótidas internas, artérias cerebrais anteriores, médias e posteriores de calibre, trajeto e contornos preservados.
Segmento intracraniano das artérias vertebrais e artéria basilar de calibre, trajeto e contornos preservados
Demais artérias intracranianas detectadas por esta técnica apresentando calibre, trajeto e contornos preservados.
Ausência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Crânio:\\nSistema ventricular de morfologia e dimensões preservadas.\\nNão há desvio de estruturas da linha mediana.\\nCisternas e sulcos corticais de amplitude dentro dos limites normais.\\nParênquima encefálico com coeficientes de atenuação habituais.\\nAusência de coleções extra-axiais nos cortes obtidos.\\nAngio Arterial:\\nSegmentos intracranianos das artérias carótidas internas, artérias cerebrais anteriores, médias e posteriores de calibre, trajeto e contornos preservados.\\nSegmento intracraniano das artérias vertebrais e artéria basilar de calibre, trajeto e contornos preservados\\nDemais artérias intracranianas detectadas por esta técnica apresentando calibre, trajeto e contornos preservados.\\nAusência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.", "impressao": "Exame dentro dos padrões da normalidade.\\nNão há evidências de estenoses ou aneurismas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Crânio e Angiotomografia Arterial Intracraniana'
    AND ativo = 1
);

-- [092] Tomografia Computadorizada do Crânio e Angiotomografia Arterial e Venosa Intracr
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Crânio e Angiotomografia Arterial e Venosa Intracraniana',
    'TC',
    'Crânio:
Sistema ventricular de morfologia e dimensões preservadas.
Não há desvio de estruturas da linha mediana.
Cisternas e sulcos corticais de amplitude dentro dos limites normais.
Parênquima encefálico com coeficientes de atenuação habituais.
Ausência de coleções extra-axiais nos cortes obtidos.
Angio Arterial:
Segmentos intracranianos das artérias carótidas internas, artérias cerebrais anteriores, médias e posteriores de calibre, trajeto e contornos preservados.
Segmento intracraniano das artérias vertebrais e artéria basilar de calibre, trajeto e contornos preservados
Demais artérias intracranianas detectadas por esta técnica apresentando calibre, trajeto e contornos preservados.
Ausência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.
Angio Venosa:
Seio sagital superior, veia de Galeno, veias cerebrais internas, seio reto, seios transversos e sigmóides, e porção cranial das veias jugulares internas de calibre, trajeto e fluxo preservados.
Não foram observados sinais de circulação patológica.
XXX Assimetria do calibre e fluxo dos seios transversos, sendo menor à XX, mais comumente significativo de hipoplasia deste lado.
XXX Seio sagital superior, seio reto, veia cerebral magna e cerebrais internas com fluxo preservado.
XXX Imagens arredondadas junto ao seio XX com atenuação semelhante ao líquor, compatíveis com granulações aracnoídeas.
Bulbos jugulares e porções proximais das veias jugulares internas pérvios.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Crânio:\\nSistema ventricular de morfologia e dimensões preservadas.\\nNão há desvio de estruturas da linha mediana.\\nCisternas e sulcos corticais de amplitude dentro dos limites normais.\\nParênquima encefálico com coeficientes de atenuação habituais.\\nAusência de coleções extra-axiais nos cortes obtidos.\\nAngio Arterial:\\nSegmentos intracranianos das artérias carótidas internas, artérias cerebrais anteriores, médias e posteriores de calibre, trajeto e contornos preservados.\\nSegmento intracraniano das artérias vertebrais e artéria basilar de calibre, trajeto e contornos preservados\\nDemais artérias intracranianas detectadas por esta técnica apresentando calibre, trajeto e contornos preservados.\\nAusência de estenoses hemodinamicamente significativas ou dilatações aneurismáticas.\\nAngio Venosa:\\nSeio sagital superior, veia de Galeno, veias cerebrais internas, seio reto, seios transversos e sigmóides, e porção cranial das veias jugulares internas de calibre, trajeto e fluxo preservados.\\nNão foram observados sinais de circulação patológica.\\nXXX Assimetria do calibre e fluxo dos seios transversos, sendo menor à XX, mais comumente significativo de hipoplasia deste lado.\\nXXX Seio sagital superior, seio reto, veia cerebral magna e cerebrais internas com fluxo preservado.\\nXXX Imagens arredondadas junto ao seio XX com atenuação semelhante ao líquor, compatíveis com granulações aracnoídeas.\\nBulbos jugulares e porções proximais das veias jugulares internas pérvios.", "impressao": "Exame dentro dos padrões da normalidade. Não há evidências de estenoses ou aneurismas.  Não há sinais de trombose venosa recente.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Crânio e Angiotomografia Arterial e Venosa Intracraniana'
    AND ativo = 1
);

-- [093] Tomografia Computadorizada do Crânio e Angiotomografia Venosa Intracraniana
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Crânio e Angiotomografia Venosa Intracraniana',
    'TC',
    'Crânio:
Sistema ventricular de morfologia e dimensões preservadas.
Não há desvio de estruturas da linha mediana.
Cisternas e sulcos corticais de amplitude dentro dos limites normais.
Parênquima encefálico com coeficientes de atenuação habituais.
Ausência de coleções extra-axiais nos cortes obtidos.
Angio Venosa:
Seio sagital superior, veia de Galeno, veias cerebrais internas, seio reto, seios transversos e sigmóides, e porção cranial das veias jugulares internas de calibre, trajeto e fluxo preservados.
Não foram observados sinais de circulação patológica.
XXX Assimetria do calibre e fluxo dos seios transversos, sendo menor à XX, mais comumente significativo de hipoplasia deste lado.
XXX Seio sagital superior, seio reto, veia cerebral magna e cerebrais internas com fluxo preservado.
XXX Imagens arredondadas junto ao seio XX com atenuação semelhante ao líquor, compatíveis com granulações aracnoídeas.
Bulbos jugulares e porções proximais das veias jugulares internas pérvios.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração intravenosa de meio de contraste iodado.", "achados": "Crânio:\\nSistema ventricular de morfologia e dimensões preservadas.\\nNão há desvio de estruturas da linha mediana.\\nCisternas e sulcos corticais de amplitude dentro dos limites normais.\\nParênquima encefálico com coeficientes de atenuação habituais.\\nAusência de coleções extra-axiais nos cortes obtidos.\\nAngio Venosa:\\nSeio sagital superior, veia de Galeno, veias cerebrais internas, seio reto, seios transversos e sigmóides, e porção cranial das veias jugulares internas de calibre, trajeto e fluxo preservados.\\nNão foram observados sinais de circulação patológica.\\nXXX Assimetria do calibre e fluxo dos seios transversos, sendo menor à XX, mais comumente significativo de hipoplasia deste lado.\\nXXX Seio sagital superior, seio reto, veia cerebral magna e cerebrais internas com fluxo preservado.\\nXXX Imagens arredondadas junto ao seio XX com atenuação semelhante ao líquor, compatíveis com granulações aracnoídeas.\\nBulbos jugulares e porções proximais das veias jugulares internas pérvios.", "impressao": "Exame dentro dos padrões da normalidade.\\nNão há sinais de trombose venosa recente.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Crânio e Angiotomografia Venosa Intracraniana'
    AND ativo = 1
);

-- [094] Tomografia Computadorizada do Crânio e Angiotomografia Arterial Intracraniana e 
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Crânio e Angiotomografia Arterial Intracraniana e Cervical Protocolo Avc - Laudo Estruturado',
    'TC',
    'Sistema ventricular de morfologia e dimensões preservadas.
Não há desvio de estruturas da linha mediana.
Cisternas e sulcos corticais de amplitude dentro dos limites normais.
Parênquima encefálico com coeficientes de atenuação habituais.
Ausência de áreas sugestivas de isquemia aguda ao método.
Não há sinais de hemorragias recentes.
Ausência de coleções extra-axiais nos cortes obtidos.
⚠️⚠️ Acentuação difusa dos sulcos e fissuras encefálicos com ectasia compensatória do sistema ventricular.
⚠️⚠️ Focos hipoatenuantes na substância branca supratentorial, sem efeito expansivo, inespecíficos, mas que podem representar gliose decorrente de microangiopatia.
Arco aórtico, tronco braquiocefálico e porções proximais das artérias subclávias pérvias, com trajeto e calibre preservados.
Origem habitual dos vasos no arco aórtico.
Artérias carótidas comuns, segmentos cervicais das carótidas internas e externas pérvias, com trajeto e calibre preservados.
Segmentos cervicais das artérias vertebrais pérvias, com trajeto e calibre preservados.
Segmentos intracranianos das artérias carótidas internas, artérias cerebrais anteriores, médias e posteriores pérvias, com trajeto e calibre preservados.
Segmento intracraniano das artérias vertebrais e artéria basilar de calibre, trajeto e contornos preservados
Demais artérias intracranianas detectadas por esta técnica sem particularidades.
Ausência de estenoses significativas ou dilatações aneurismáticas.
⚠️⚠️ Ateromatose difusa, com placas calcificadas e fibrocalcificadas no arco aórtico, ramos supra-aórticos, artérias carótidas internas (emergências dos segmentos cervicais e segmentos cavernosos) e artéria vertebrais, sem sinais de estenoses significativas.
Se AVCi RECENTE no território de ACM, assinale as regiões acometidas (caso contrário, deixe em branco):
[  ] Território M1 (opérculo frontal)
[  ] Território M2 (porção anterior do lobo temporal)
[  ] Território M3 (porção posterior do lobo temporal)
[  ] Território M4 (cranial à M1)
[  ] Território M5 (cranial à M2)
[  ] Território M6 (cranial à M3)
[  ] Território Ínsula
[  ] Território Caudado
[  ] Território Cápsula interna
[  ] Território Núcleo lentiforme
Escore ASPECTS (subtrair 1 para cada região acometida) = 10
Se OCLUSÃO de grande tronco arterial, assinale os segmentos ocluídos (caso contrário, deixe em branco)?
[  ] Carótida comum DIREITA
[  ] Carótida interna DIREITA
[  ] Segmento M1 e/ou M2 da ACM DIREITA
[  ] Segmento A1 e/ou A2 da ACA DIREITA
[  ] Segmento P1 e/ou P2 da ACP DIREITA
[  ] Vertebral DIREITA
[  ] Carótida comum ESQUERDA
[  ] Carótida interna ESQUERDA
[  ] Segmento M1 e/ou M2 da ACM ESQUERDA
[  ] Segmento A1 e/ou A2 da ACA ESQUERDA
[  ] Segmento P1 e/ou P2 da ACP ESQUERDA
[  ] Vertebral ESQUERDA
[  ] Basilar',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa do meio de contraste.", "achados": "Sistema ventricular de morfologia e dimensões preservadas.\\nNão há desvio de estruturas da linha mediana.\\nCisternas e sulcos corticais de amplitude dentro dos limites normais.\\nParênquima encefálico com coeficientes de atenuação habituais.\\nAusência de áreas sugestivas de isquemia aguda ao método.\\nNão há sinais de hemorragias recentes.\\nAusência de coleções extra-axiais nos cortes obtidos.\\n⚠️⚠️ Acentuação difusa dos sulcos e fissuras encefálicos com ectasia compensatória do sistema ventricular.\\n⚠️⚠️ Focos hipoatenuantes na substância branca supratentorial, sem efeito expansivo, inespecíficos, mas que podem representar gliose decorrente de microangiopatia.\\nArco aórtico, tronco braquiocefálico e porções proximais das artérias subclávias pérvias, com trajeto e calibre preservados.\\nOrigem habitual dos vasos no arco aórtico.\\nArtérias carótidas comuns, segmentos cervicais das carótidas internas e externas pérvias, com trajeto e calibre preservados.\\nSegmentos cervicais das artérias vertebrais pérvias, com trajeto e calibre preservados.\\nSegmentos intracranianos das artérias carótidas internas, artérias cerebrais anteriores, médias e posteriores pérvias, com trajeto e calibre preservados.\\nSegmento intracraniano das artérias vertebrais e artéria basilar de calibre, trajeto e contornos preservados\\nDemais artérias intracranianas detectadas por esta técnica sem particularidades.\\nAusência de estenoses significativas ou dilatações aneurismáticas.\\n⚠️⚠️ Ateromatose difusa, com placas calcificadas e fibrocalcificadas no arco aórtico, ramos supra-aórticos, artérias carótidas internas (emergências dos segmentos cervicais e segmentos cavernosos) e artéria vertebrais, sem sinais de estenoses significativas.\\nSe AVCi RECENTE no território de ACM, assinale as regiões acometidas (caso contrário, deixe em branco):\\n[  ] Território M1 (opérculo frontal)\\n[  ] Território M2 (porção anterior do lobo temporal)\\n[  ] Território M3 (porção posterior do lobo temporal)\\n[  ] Território M4 (cranial à M1)\\n[  ] Território M5 (cranial à M2)\\n[  ] Território M6 (cranial à M3)\\n[  ] Território Ínsula\\n[  ] Território Caudado\\n[  ] Território Cápsula interna\\n[  ] Território Núcleo lentiforme\\nEscore ASPECTS (subtrair 1 para cada região acometida) = 10\\nSe OCLUSÃO de grande tronco arterial, assinale os segmentos ocluídos (caso contrário, deixe em branco)?\\n[  ] Carótida comum DIREITA\\n[  ] Carótida interna DIREITA\\n[  ] Segmento M1 e/ou M2 da ACM DIREITA\\n[  ] Segmento A1 e/ou A2 da ACA DIREITA\\n[  ] Segmento P1 e/ou P2 da ACP DIREITA\\n[  ] Vertebral DIREITA\\n[  ] Carótida comum ESQUERDA\\n[  ] Carótida interna ESQUERDA\\n[  ] Segmento M1 e/ou M2 da ACM ESQUERDA\\n[  ] Segmento A1 e/ou A2 da ACA ESQUERDA\\n[  ] Segmento P1 e/ou P2 da ACP ESQUERDA\\n[  ] Vertebral ESQUERDA\\n[  ] Basilar", "impressao": "Não foram identificados sinais sugestivos de AVCh ou AVCi.\\nAusência de estenoses significativas ou dilatações aneurismáticas.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Crânio e Angiotomografia Arterial Intracraniana e Cervical Protocolo Avc - Laudo Estruturado'
    AND ativo = 1
);

-- [095] Tomografia Computadorizada do Crânio Protocolo Avc - Laudo Estruturado
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Crânio Protocolo Avc - Laudo Estruturado',
    'TC',
    'Sistema ventricular de morfologia e dimensões preservadas.
Não há desvio de estruturas da linha mediana.
Cisternas e sulcos corticais de amplitude dentro dos limites normais.
Parênquima encefálico com coeficientes de atenuação habituais.
Ausência de áreas sugestivas de isquemia aguda ao método.
Não há sinais de hemorragias recentes.
Ausência de coleções extra-axiais nos cortes obtidos.
Não há áreas de realce anômalo pós-contraste.
⚠️⚠️ Acentuação difusa dos sulcos e fissuras encefálicos com ectasia compensatória do sistema ventricular.
⚠️⚠️ Focos hipoatenuantes na substância branca supratentorial, sem efeito expansivo, inespecíficos, mas que podem representar gliose decorrente de microangiopatia.
Se AVCi RECENTE no território de ACM, assinale as regiões acometidas (caso contrário, deixe em branco):
[  ] Território M1 (opérculo frontal)
[  ] Território M2 (porção anterior do lobo temporal)
[  ] Território M3 (porção posterior do lobo temporal)
[  ] Território M4 (cranial à M1)
[  ] Território M5 (cranial à M2)
[  ] Território M6 (cranial à M3)
[  ] Território Ínsula
[  ] Território Caudado
[  ] Território Cápsula interna
[  ] Território Núcleo lentiforme
Escore ASPECTS (subtrair 1 para cada região acometida) = 10',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa do meio de contraste.", "achados": "Sistema ventricular de morfologia e dimensões preservadas.\\nNão há desvio de estruturas da linha mediana.\\nCisternas e sulcos corticais de amplitude dentro dos limites normais.\\nParênquima encefálico com coeficientes de atenuação habituais.\\nAusência de áreas sugestivas de isquemia aguda ao método.\\nNão há sinais de hemorragias recentes.\\nAusência de coleções extra-axiais nos cortes obtidos.\\nNão há áreas de realce anômalo pós-contraste.\\n⚠️⚠️ Acentuação difusa dos sulcos e fissuras encefálicos com ectasia compensatória do sistema ventricular.\\n⚠️⚠️ Focos hipoatenuantes na substância branca supratentorial, sem efeito expansivo, inespecíficos, mas que podem representar gliose decorrente de microangiopatia.\\nSe AVCi RECENTE no território de ACM, assinale as regiões acometidas (caso contrário, deixe em branco):\\n[  ] Território M1 (opérculo frontal)\\n[  ] Território M2 (porção anterior do lobo temporal)\\n[  ] Território M3 (porção posterior do lobo temporal)\\n[  ] Território M4 (cranial à M1)\\n[  ] Território M5 (cranial à M2)\\n[  ] Território M6 (cranial à M3)\\n[  ] Território Ínsula\\n[  ] Território Caudado\\n[  ] Território Cápsula interna\\n[  ] Território Núcleo lentiforme\\nEscore ASPECTS (subtrair 1 para cada região acometida) = 10", "impressao": "Não foram identificados sinais sugestivos de isquemia aguda ou hemorragias recentes.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Crânio Protocolo Avc - Laudo Estruturado'
    AND ativo = 1
);

-- [096] Tomografia Computadorizada do Crânio
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Crânio',
    'TC',
    'Sistema ventricular de morfologia e dimensões preservadas.
Não há desvio de estruturas da linha mediana.
Cisternas e sulcos corticais de amplitude dentro dos limites normais.
Parênquima encefálico com coeficientes de atenuação habituais.
Ausência de coleções extra-axiais nos cortes obtidos.
### Não há evidência de realces anômalos.',
    '{"indicacao": "", "tecnica": "Imagens obtidas antes e após a administração endovenosa do meio de contraste.", "achados": "Sistema ventricular de morfologia e dimensões preservadas.\\nNão há desvio de estruturas da linha mediana.\\nCisternas e sulcos corticais de amplitude dentro dos limites normais.\\nParênquima encefálico com coeficientes de atenuação habituais.\\nAusência de coleções extra-axiais nos cortes obtidos.\\n### Não há evidência de realces anômalos.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Crânio'
    AND ativo = 1
);

-- [097] Tomografia Computadorizada da Escápula Esquerda
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada da Escápula Esquerda',
    'TC',
    'Estruturas ósseas de formato habitual, sem sinais de fraturas ou lesões ósseas focais com características agressivas.
Articulação acromioclavicular de contornos regulares.
Articulação glenoumeral congruente e de contornos regulares. Não foi identificado derrame articular significativo.
Planos musculares apresentam trofismo preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa de contraste iodado.", "achados": "Estruturas ósseas de formato habitual, sem sinais de fraturas ou lesões ósseas focais com características agressivas.\\nArticulação acromioclavicular de contornos regulares.\\nArticulação glenoumeral congruente e de contornos regulares. Não foi identificado derrame articular significativo.\\nPlanos musculares apresentam trofismo preservado.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada da Escápula Esquerda'
    AND ativo = 1
);

-- [098] Tomografia Computadorizada da Face
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada da Face',
    'TC',
    'Estruturas ósseas preservadas.
Globos oculares simétricos e de dimensões normais, com atenuação característica.
Gorduras extraconal e intraconal com atenuação preservada.
Cavidades paranasais normoaeradas.
Glândulas parótidas e submandibulares sem alterações.
Rino e orofaringe apresentando atenuação preservada.
Ausência de coleções.
####  Não se observam realces anômalos.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa do meio de contraste.", "achados": "Estruturas ósseas preservadas.\\nGlobos oculares simétricos e de dimensões normais, com atenuação característica.\\nGorduras extraconal e intraconal com atenuação preservada.\\nCavidades paranasais normoaeradas.\\nGlândulas parótidas e submandibulares sem alterações.\\nRino e orofaringe apresentando atenuação preservada.\\nAusência de coleções.\\n####  Não se observam realces anômalos.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada da Face'
    AND ativo = 1
);

-- [099] Tomografia Computadorizada das Mãos (direita e Esquerda)
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada das Mãos (direita e Esquerda)',
    'TC',
    'Imagens obtidas com a administração endovenosa do meio de contraste.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa do meio de contraste.", "achados": "", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada das Mãos (direita e Esquerda)'
    AND ativo = 1
);

-- [100] Mão Direita:
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Mão Direita:',
    'TC',
    '',
    '{"indicacao": "", "tecnica": "", "achados": "", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Mão Direita:'
    AND ativo = 1
);

-- [101] Mão Esquerda:
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Mão Esquerda:',
    'TC',
    '',
    '{"indicacao": "", "tecnica": "", "achados": "", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Mão Esquerda:'
    AND ativo = 1
);

-- [102] Tomografia Computadorizada da Maxila
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada da Maxila',
    'TC',
    'Maxila de morfologia e atenuação habitual, sem evidência de fraturas.
Demais estruturas ósseas sem particularidades.
Globos oculares simétricos e de dimensões normais, com atenuação característica.
Gorduras extraconal e intraconal com atenuação preservada.
Cavidades paranasais normoaeradas.
Planos musculares e tendíneos sem alterações tomográficas.',
    '{"indicacao": "", "tecnica": "Imagens obtidas sem a administração endovenosa do meio de contraste.", "achados": "Maxila de morfologia e atenuação habitual, sem evidência de fraturas.\\nDemais estruturas ósseas sem particularidades.\\nGlobos oculares simétricos e de dimensões normais, com atenuação característica.\\nGorduras extraconal e intraconal com atenuação preservada.\\nCavidades paranasais normoaeradas.\\nPlanos musculares e tendíneos sem alterações tomográficas.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada da Maxila'
    AND ativo = 1
);

-- [103] Tomografia Computadorizada das Órbitas
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada das Órbitas',
    'TC',
    'Estruturas ósseas sem alterações.
Globos oculares apresentando atenuação preservada.
Musculatura extrínseca, gordura orbitária intra e extraconal e nervos ópticos preservados.
Glândulas lacrimais sem particularidades.',
    '{"indicacao": "", "tecnica": "Imagens obtidas sem a administração endovenosa do meio de contraste.", "achados": "Estruturas ósseas sem alterações.\\nGlobos oculares apresentando atenuação preservada.\\nMusculatura extrínseca, gordura orbitária intra e extraconal e nervos ópticos preservados.\\nGlândulas lacrimais sem particularidades.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada das Órbitas'
    AND ativo = 1
);

-- [104] Tomografia Computadorizada dos Ossos Temporais
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada dos Ossos Temporais',
    'TC',
    'Osso temporal DIREITO:
Conduto auditivo externo e esporão ósseo sem particularidades.
Membrana timpânica de aspecto usual.
Mastoide normopneumatizada.
Caixa timpânica normoaerada.
Janelas oval e redonda livres.
Cadeia ossicular com topografia, morfologia e densidade preservadas.
Cóclea, vestíbulo, canais semicirculares e conduto auditivo interno preservados.
Cápsula ótica com densidade preservada.
Canal do nervo facial preservado.
Canal carotídeo e bulbo jugular sem particularidades.
Osso temporal ESQUERDO:
Conduto auditivo externo e esporão ósseo sem particularidades.
Membrana timpânica de aspecto usual.
Mastoide normopneumatizada.
Caixa timpânica normoaerada.
Janelas oval e redonda livres.
Cadeia ossicular com topografia, morfologia e densidade preservadas.
Cóclea, vestíbulo, canais semicirculares e conduto auditivo interno preservados.
Cápsula ótica com densidade preservada.
Canal do nervo facial preservado.
Canal carotídeo e bulbo jugular sem particularidades.',
    '{"indicacao": "", "tecnica": "Imagens obtidas sem a administração endovenosa do meio de contraste.", "achados": "Osso temporal DIREITO:\\nConduto auditivo externo e esporão ósseo sem particularidades.\\nMembrana timpânica de aspecto usual.\\nMastoide normopneumatizada.\\nCaixa timpânica normoaerada.\\nJanelas oval e redonda livres.\\nCadeia ossicular com topografia, morfologia e densidade preservadas.\\nCóclea, vestíbulo, canais semicirculares e conduto auditivo interno preservados.\\nCápsula ótica com densidade preservada.\\nCanal do nervo facial preservado.\\nCanal carotídeo e bulbo jugular sem particularidades.\\nOsso temporal ESQUERDO:\\nConduto auditivo externo e esporão ósseo sem particularidades.\\nMembrana timpânica de aspecto usual.\\nMastoide normopneumatizada.\\nCaixa timpânica normoaerada.\\nJanelas oval e redonda livres.\\nCadeia ossicular com topografia, morfologia e densidade preservadas.\\nCóclea, vestíbulo, canais semicirculares e conduto auditivo interno preservados.\\nCápsula ótica com densidade preservada.\\nCanal do nervo facial preservado.\\nCanal carotídeo e bulbo jugular sem particularidades.", "impressao": "Osso temporal DIREITO:\\n- Estruturas avaliadas dentro dos padrões da normalidade.\\nOsso temporal ESQUERDO:\\n- Estruturas avaliadas dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada dos Ossos Temporais'
    AND ativo = 1
);

-- [105] Tomografia Computadorizada do Pescoço
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Pescoço',
    'TC',
    'Faringe e laringe apresentando atenuação preservada.
Glândulas parótidas e submandibulares sem alterações.
Não se evidenciam linfonodomegalias.
Glândula tireoide sem particularidades.
Aspecto normal dos vasos do pescoço.
Estruturas ósseas preservadas.
#### Não se observam realces focais anômalos.',
    '{"indicacao": "", "tecnica": "Imagens obtidas com a administração endovenosa do meio de contraste.", "achados": "Faringe e laringe apresentando atenuação preservada.\\nGlândulas parótidas e submandibulares sem alterações.\\nNão se evidenciam linfonodomegalias.\\nGlândula tireoide sem particularidades.\\nAspecto normal dos vasos do pescoço.\\nEstruturas ósseas preservadas.\\n#### Não se observam realces focais anômalos.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Pescoço'
    AND ativo = 1
);

-- [106] Tomografia Computadorizada do Quadril, Joelho e Tornozelo Esquerdos para Avaliaç
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Quadril, Joelho e Tornozelo Esquerdos para Avaliação de Tagt, Anteversão Femoral e Torção Tibial',
    'TC',
    'Ângulo de anteversão femoral: ___º (+ anteversão / - retroversão).
Ângulo de torção tibial: ___º (+ externa / - interna).
Valgismo do aparelho extensor conservado.
Patela tipo I / II / III / IV de Wiberg.
Patela tópica em relação ao sulco troclear em extensão e às manobras de semiflexão.
<⚠️⚠️> Patela com báscula e subluxação lateral em extensão, com redução nas manobras de semiflexão.
TILT patelar de ___º em extensão, ___º em semiflexão de 15º e de ___º em semiflexão de 30º (normal: < 20º).
Altura patelar segundo o índice de Caton-Deschamps medido na aquisição de semiflexão de 30º de cerca de ___ (normal / pouco alta / alta) (normal: 0,8 a 1,2).
Medida de TAGT realizada em extensão de ___ mm (normal: 15 ± 4 mm)
Tróclea femoral rasa / displásica / com morfologia preservada. Profundidade do sulco troclear pelo método de Pfirrmann de ___ mm (normal: > 3 mm).
Outros achados:
Não há evidências de fraturas.
Espaços articulares conservados.
Não há sinais de derrame articular significativo.
Ventres musculares com trofismo preservado.',
    '{"indicacao": "", "tecnica": "Foram obtidas imagens por aquisição volumétrica, com reformatações multiplanares.", "achados": "Ângulo de anteversão femoral: ___º (+ anteversão / - retroversão).\\nÂngulo de torção tibial: ___º (+ externa / - interna).\\nValgismo do aparelho extensor conservado.\\nPatela tipo I / II / III / IV de Wiberg.\\nPatela tópica em relação ao sulco troclear em extensão e às manobras de semiflexão.\\n<⚠️⚠️> Patela com báscula e subluxação lateral em extensão, com redução nas manobras de semiflexão.\\nTILT patelar de ___º em extensão, ___º em semiflexão de 15º e de ___º em semiflexão de 30º (normal: < 20º).\\nAltura patelar segundo o índice de Caton-Deschamps medido na aquisição de semiflexão de 30º de cerca de ___ (normal / pouco alta / alta) (normal: 0,8 a 1,2).\\nMedida de TAGT realizada em extensão de ___ mm (normal: 15 ± 4 mm)\\nTróclea femoral rasa / displásica / com morfologia preservada. Profundidade do sulco troclear pelo método de Pfirrmann de ___ mm (normal: > 3 mm).\\nOutros achados:\\nNão há evidências de fraturas.\\nEspaços articulares conservados.\\nNão há sinais de derrame articular significativo.\\nVentres musculares com trofismo preservado.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Quadril, Joelho e Tornozelo Esquerdos para Avaliação de Tagt, Anteversão Femoral e Torção Tibial'
    AND ativo = 1
);

-- [107] Tomografia Computadorizada do Quadril, Joelho e Tornozelo Direitos para Avaliaçã
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Quadril, Joelho e Tornozelo Direitos para Avaliação de Anteversão Femoral e Torção Tibial',
    'TC',
    'Ângulo de anteversão femoral: ___º (+ anteversão / - retroversão).
Ângulo de torção tibial: ___º (+ externa / - interna).
Valgismo do aparelho extensor conservado.
Estruturas ósseas sem alterações significativas ao método.
Espaços articulares conservados.
Planos miotendíneos sem alterações detectáveis ao método.',
    '{"indicacao": "", "tecnica": "Foram obtidas imagens por aquisição volumétrica, com reformatações multiplanares.", "achados": "Ângulo de anteversão femoral: ___º (+ anteversão / - retroversão).\\nÂngulo de torção tibial: ___º (+ externa / - interna).\\nValgismo do aparelho extensor conservado.\\nEstruturas ósseas sem alterações significativas ao método.\\nEspaços articulares conservados.\\nPlanos miotendíneos sem alterações detectáveis ao método.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Quadril, Joelho e Tornozelo Direitos para Avaliação de Anteversão Femoral e Torção Tibial'
    AND ativo = 1
);

-- [108] Tomografia Computadorizada dos Quadris, Joelhos e Tornozelos Bilaterais para Ava
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada dos Quadris, Joelhos e Tornozelos Bilaterais para Avaliação de Anteversão Femoral e Torção Tibial',
    'TC',
    'Membro inferior DIREITO:
Ângulo de anteversão femoral: ___º (+ anteversão / - retroversão).
Ângulo de torção tibial: ___º (+ externa / - interna).
Valgismo do aparelho extensor conservado.
Estruturas ósseas sem alterações significativas ao método.
Espaços articulares conservados.
Planos miotendíneos sem alterações detectáveis ao método.
Membro inferior ESQUERDO:
Ângulo de anteversão femoral: ___º (+ anteversão / - retroversão).
Ângulo de torção tibial: ___º (+ externa / - interna).
Valgismo do aparelho extensor conservado.
Estruturas ósseas sem alterações significativas ao método.
Espaços articulares conservados.
Planos miotendíneos sem alterações detectáveis ao método.',
    '{"indicacao": "", "tecnica": "Foram obtidas imagens por aquisição volumétrica, com reformatações multiplanares.", "achados": "Membro inferior DIREITO:\\nÂngulo de anteversão femoral: ___º (+ anteversão / - retroversão).\\nÂngulo de torção tibial: ___º (+ externa / - interna).\\nValgismo do aparelho extensor conservado.\\nEstruturas ósseas sem alterações significativas ao método.\\nEspaços articulares conservados.\\nPlanos miotendíneos sem alterações detectáveis ao método.\\nMembro inferior ESQUERDO:\\nÂngulo de anteversão femoral: ___º (+ anteversão / - retroversão).\\nÂngulo de torção tibial: ___º (+ externa / - interna).\\nValgismo do aparelho extensor conservado.\\nEstruturas ósseas sem alterações significativas ao método.\\nEspaços articulares conservados.\\nPlanos miotendíneos sem alterações detectáveis ao método.", "impressao": "Membro inferior DIREITO:\\n- Estruturas avaliadas de aspecto preservado.\\nMembro inferior ESQUERDO:\\n- Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada dos Quadris, Joelhos e Tornozelos Bilaterais para Avaliação de Anteversão Femoral e Torção Tibial'
    AND ativo = 1
);

-- [109] Tomografia Computadorizada do Quadril, Coxa e Joelho Esquerdos para Avaliação de
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Quadril, Coxa e Joelho Esquerdos para Avaliação de Anteversão Femoral',
    'TC',
    'Ângulo de anteversão femoral: ___º (+ anteversão / - retroversão).
Estruturas ósseas sem alterações significativas ao método.
Espaços articulares conservados.
Planos miotendíneos sem alterações detectáveis ao método',
    '{"indicacao": "", "tecnica": "Foram obtidas imagens por aquisição volumétrica, com reformatações multiplanares.", "achados": "Ângulo de anteversão femoral: ___º (+ anteversão / - retroversão).\\nEstruturas ósseas sem alterações significativas ao método.\\nEspaços articulares conservados.\\nPlanos miotendíneos sem alterações detectáveis ao método", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Quadril, Coxa e Joelho Esquerdos para Avaliação de Anteversão Femoral'
    AND ativo = 1
);

-- [110] Tomografia Computadorizada do Quadril, Coxa e Joelho Direitos para Avaliação de 
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada do Quadril, Coxa e Joelho Direitos para Avaliação de Anteversão Femoral',
    'TC',
    'Ângulo de anteversão femoral: ___º (+ anteversão / - retroversão).
Estruturas ósseas sem alterações significativas ao método.
Espaços articulares conservados.
Planos miotendíneos sem alterações detectáveis ao método',
    '{"indicacao": "", "tecnica": "Foram obtidas imagens por aquisição volumétrica, com reformatações multiplanares.", "achados": "Ângulo de anteversão femoral: ___º (+ anteversão / - retroversão).\\nEstruturas ósseas sem alterações significativas ao método.\\nEspaços articulares conservados.\\nPlanos miotendíneos sem alterações detectáveis ao método", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada do Quadril, Coxa e Joelho Direitos para Avaliação de Anteversão Femoral'
    AND ativo = 1
);

-- [111] Tomografia de Abdome Total Laudo Estruturado – Recist
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia de Abdome Total Laudo Estruturado – Recist',
    'TC',
    'XXXX Estudo base.
XXXX Exame comparado ao estudo de base realizado em:
XXXX Estudo anterior disponível para comparação realizado em:
Lesões-alvo:
⚠️⚠️ Máx. 5 lesões, até 2 por órgão; (i) órgãos > 1,0 cm no MAIOR eixo e (ii) linfonodos > 1,5 cm no MENOR eixo ⚠️⚠️
1.
2.
3.
4.
5.
Somatória das lesões-alvo:
Lesões não-alvo:
-
Lesões novas:
XXX Estudo base (inicial).
XXX Não há evidências de novas lesões.
XXX Surgiu lesão ___.
Demais achados:
- Fígado, pâncreas, baço, rins, aorta, adrenais e bexiga sem particularidades.
- Ausência de linfonodomegalias abdominais ou de líquido livre.',
    '{"indicacao": "", "tecnica": "Imagens obtidas antes e após a administração do meio de contraste intravenoso. Análise realizada segundo o protocolo RECIST 1.1 (Response Evaluation Criteria in Solid Tumors).", "achados": "XXXX Estudo base.\\nXXXX Exame comparado ao estudo de base realizado em:\\nXXXX Estudo anterior disponível para comparação realizado em:\\nLesões-alvo:\\n⚠️⚠️ Máx. 5 lesões, até 2 por órgão; (i) órgãos > 1,0 cm no MAIOR eixo e (ii) linfonodos > 1,5 cm no MENOR eixo ⚠️⚠️\\n1.\\n2.\\n3.\\n4.\\n5.\\nSomatória das lesões-alvo:\\nLesões não-alvo:\\n-\\nLesões novas:\\nXXX Estudo base (inicial).\\nXXX Não há evidências de novas lesões.\\nXXX Surgiu lesão ___.\\nDemais achados:\\n- Fígado, pâncreas, baço, rins, aorta, adrenais e bexiga sem particularidades.\\n- Ausência de linfonodomegalias abdominais ou de líquido livre.", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia de Abdome Total Laudo Estruturado – Recist'
    AND ativo = 1
);

-- [112] Tomografia de Crânio, Face, Pescoço, Tórax, Abdome Total Laudo Estruturado – Rec
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia de Crânio, Face, Pescoço, Tórax, Abdome Total Laudo Estruturado – Recist',
    'TC',
    'XXXX Estudo base.
XXXX Exame comparado ao estudo de base realizado em:
XXXX Estudo anterior disponível para comparação realizado em:
Lesões-alvo:
⚠️⚠️ Máx. 5 lesões, até 2 por órgão; (i) órgãos > 1,0 cm no MAIOR eixo e (ii) linfonodos > 1,5 cm no MENOR eixo ⚠️⚠️
1.
2.
3.
4.
5.
Somatória das lesões-alvo:
Lesões não-alvo:
-
Lesões novas:
XXX Estudo base (inicial).
XXX Não há evidências de novas lesões.
XXX Surgiu lesão ___.
Demais achados:
Crânio:
Sistema ventricular de morfologia e dimensões preservadas.
Cisternas e sulcos corticais de amplitude dentro dos limites normais.
Parênquima encefálico apresenta atenuação habitual.
Face e pescoço:
Faringe, laringe, órbitas e cavidades paranasais sem particularidades.
Glândulas parótidas, submandibulares e tireoide sem particularidades.
Não se evidenciam linfonodomegalias.
Tórax:
- Não se observam linfonodomegalias mediastinais.
- Parênquima pulmonar com atenuação preservada.
- Ausência de derrame pleural.
Abdome total:
- Fígado, pâncreas, baço, rins, aorta, adrenais e bexiga sem particularidades.
- Ausência de linfonodomegalias abdominais ou de líquido livre.',
    '{"indicacao": "", "tecnica": "Imagens obtidas antes e após a administração do meio de contraste intravenoso. Análise realizada segundo o protocolo RECIST 1.1 (Response Evaluation Criteria in Solid Tumors).", "achados": "XXXX Estudo base.\\nXXXX Exame comparado ao estudo de base realizado em:\\nXXXX Estudo anterior disponível para comparação realizado em:\\nLesões-alvo:\\n⚠️⚠️ Máx. 5 lesões, até 2 por órgão; (i) órgãos > 1,0 cm no MAIOR eixo e (ii) linfonodos > 1,5 cm no MENOR eixo ⚠️⚠️\\n1.\\n2.\\n3.\\n4.\\n5.\\nSomatória das lesões-alvo:\\nLesões não-alvo:\\n-\\nLesões novas:\\nXXX Estudo base (inicial).\\nXXX Não há evidências de novas lesões.\\nXXX Surgiu lesão ___.\\nDemais achados:\\nCrânio:\\nSistema ventricular de morfologia e dimensões preservadas.\\nCisternas e sulcos corticais de amplitude dentro dos limites normais.\\nParênquima encefálico apresenta atenuação habitual.\\nFace e pescoço:\\nFaringe, laringe, órbitas e cavidades paranasais sem particularidades.\\nGlândulas parótidas, submandibulares e tireoide sem particularidades.\\nNão se evidenciam linfonodomegalias.\\nTórax:\\n- Não se observam linfonodomegalias mediastinais.\\n- Parênquima pulmonar com atenuação preservada.\\n- Ausência de derrame pleural.\\nAbdome total:\\n- Fígado, pâncreas, baço, rins, aorta, adrenais e bexiga sem particularidades.\\n- Ausência de linfonodomegalias abdominais ou de líquido livre.", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia de Crânio, Face, Pescoço, Tórax, Abdome Total Laudo Estruturado – Recist'
    AND ativo = 1
);

-- [113] Tomografia de Crânio, Face, Tórax, Abdome Total Laudo Estruturado – Recist
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia de Crânio, Face, Tórax, Abdome Total Laudo Estruturado – Recist',
    'TC',
    'XXXX Estudo base.
XXXX Exame comparado ao estudo de base realizado em:
XXXX Estudo anterior disponível para comparação realizado em:
Lesões-alvo:
⚠️⚠️ Máx. 5 lesões, até 2 por órgão; (i) órgãos > 1,0 cm no MAIOR eixo e (ii) linfonodos > 1,5 cm no MENOR eixo ⚠️⚠️
1.
2.
3.
4.
5.
Somatória das lesões-alvo:
Lesões não-alvo:
-
Lesões novas:
XXX Estudo base (inicial).
XXX Não há evidências de novas lesões.
XXX Surgiu lesão ___.
Demais achados:
Crânio:
Sistema ventricular de morfologia e dimensões preservadas.
Cisternas e sulcos corticais de amplitude dentro dos limites normais.
Parênquima encefálico apresenta atenuação habitual.
Face:
Rinofaringe, orofaringe, órbitas e cavidades paranasais sem particularidades.
Glândulas parótidas e submandibulares sem particularidades.
Não se evidenciam linfonodomegalias.
Tórax:
- Não se observam linfonodomegalias mediastinais.
- Parênquima pulmonar com atenuação preservada.
- Ausência de derrame pleural.
Abdome total:
- Fígado, pâncreas, baço, rins, aorta, adrenais e bexiga sem particularidades.
- Ausência de linfonodomegalias abdominais ou de líquido livre.',
    '{"indicacao": "", "tecnica": "Imagens obtidas antes e após a administração do meio de contraste intravenoso. Análise realizada segundo o protocolo RECIST 1.1 (Response Evaluation Criteria in Solid Tumors).", "achados": "XXXX Estudo base.\\nXXXX Exame comparado ao estudo de base realizado em:\\nXXXX Estudo anterior disponível para comparação realizado em:\\nLesões-alvo:\\n⚠️⚠️ Máx. 5 lesões, até 2 por órgão; (i) órgãos > 1,0 cm no MAIOR eixo e (ii) linfonodos > 1,5 cm no MENOR eixo ⚠️⚠️\\n1.\\n2.\\n3.\\n4.\\n5.\\nSomatória das lesões-alvo:\\nLesões não-alvo:\\n-\\nLesões novas:\\nXXX Estudo base (inicial).\\nXXX Não há evidências de novas lesões.\\nXXX Surgiu lesão ___.\\nDemais achados:\\nCrânio:\\nSistema ventricular de morfologia e dimensões preservadas.\\nCisternas e sulcos corticais de amplitude dentro dos limites normais.\\nParênquima encefálico apresenta atenuação habitual.\\nFace:\\nRinofaringe, orofaringe, órbitas e cavidades paranasais sem particularidades.\\nGlândulas parótidas e submandibulares sem particularidades.\\nNão se evidenciam linfonodomegalias.\\nTórax:\\n- Não se observam linfonodomegalias mediastinais.\\n- Parênquima pulmonar com atenuação preservada.\\n- Ausência de derrame pleural.\\nAbdome total:\\n- Fígado, pâncreas, baço, rins, aorta, adrenais e bexiga sem particularidades.\\n- Ausência de linfonodomegalias abdominais ou de líquido livre.", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia de Crânio, Face, Tórax, Abdome Total Laudo Estruturado – Recist'
    AND ativo = 1
);

-- [114] Tomografia de Crânio, Tórax, Abdome Total Laudo Estruturado – Recist
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia de Crânio, Tórax, Abdome Total Laudo Estruturado – Recist',
    'TC',
    'XXXX Estudo base.
XXXX Exame comparado ao estudo de base realizado em:
XXXX Estudo anterior disponível para comparação realizado em:
Lesões-alvo:
⚠️⚠️ Máx. 5 lesões, até 2 por órgão; (i) órgãos > 1,0 cm no MAIOR eixo e (ii) linfonodos > 1,5 cm no MENOR eixo ⚠️⚠️
1.
2.
3.
4.
5.
Somatória das lesões-alvo:
Lesões não-alvo:
-
Lesões novas:
XXX Estudo base (inicial).
XXX Não há evidências de novas lesões.
XXX Surgiu lesão ___.
Demais achados:
Crânio:
Sistema ventricular de morfologia e dimensões preservadas.
Cisternas e sulcos corticais de amplitude dentro dos limites normais.
Parênquima encefálico apresenta atenuação habitual.
Tórax:
- Não se observam linfonodomegalias mediastinais.
- Parênquima pulmonar com atenuação preservada.
- Ausência de derrame pleural.
Abdome total:
- Fígado, pâncreas, baço, rins, aorta, adrenais e bexiga sem particularidades.
- Ausência de linfonodomegalias abdominais ou de líquido livre.',
    '{"indicacao": "", "tecnica": "Imagens obtidas antes e após a administração do meio de contraste intravenoso. Análise realizada segundo o protocolo RECIST 1.1 (Response Evaluation Criteria in Solid Tumors).", "achados": "XXXX Estudo base.\\nXXXX Exame comparado ao estudo de base realizado em:\\nXXXX Estudo anterior disponível para comparação realizado em:\\nLesões-alvo:\\n⚠️⚠️ Máx. 5 lesões, até 2 por órgão; (i) órgãos > 1,0 cm no MAIOR eixo e (ii) linfonodos > 1,5 cm no MENOR eixo ⚠️⚠️\\n1.\\n2.\\n3.\\n4.\\n5.\\nSomatória das lesões-alvo:\\nLesões não-alvo:\\n-\\nLesões novas:\\nXXX Estudo base (inicial).\\nXXX Não há evidências de novas lesões.\\nXXX Surgiu lesão ___.\\nDemais achados:\\nCrânio:\\nSistema ventricular de morfologia e dimensões preservadas.\\nCisternas e sulcos corticais de amplitude dentro dos limites normais.\\nParênquima encefálico apresenta atenuação habitual.\\nTórax:\\n- Não se observam linfonodomegalias mediastinais.\\n- Parênquima pulmonar com atenuação preservada.\\n- Ausência de derrame pleural.\\nAbdome total:\\n- Fígado, pâncreas, baço, rins, aorta, adrenais e bexiga sem particularidades.\\n- Ausência de linfonodomegalias abdominais ou de líquido livre.", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia de Crânio, Tórax, Abdome Total Laudo Estruturado – Recist'
    AND ativo = 1
);

-- [115] Tomografia de Face, Pescoço, Tórax, Abdome Total Laudo Estruturado – Recist
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia de Face, Pescoço, Tórax, Abdome Total Laudo Estruturado – Recist',
    'TC',
    'XXXX Estudo base.
XXXX Exame comparado ao estudo de base realizado em:
XXXX Estudo anterior disponível para comparação realizado em:
Lesões-alvo:
⚠️⚠️ Máx. 5 lesões, até 2 por órgão; (i) órgãos > 1,0 cm no MAIOR eixo e (ii) linfonodos > 1,5 cm no MENOR eixo ⚠️⚠️
1.
2.
3.
4.
5.
Somatória das lesões-alvo:
Lesões não-alvo:
-
Lesões novas:
XXX Estudo base (inicial).
XXX Não há evidências de novas lesões.
XXX Surgiu lesão ___.
Demais achados:
Face e pescoço:
Faringe, laringe, órbitas e cavidades paranasais sem particularidades.
Glândulas parótidas, submandibulares e tireoide sem particularidades.
Não se evidenciam linfonodomegalias.
Tórax:
- Não se observam linfonodomegalias mediastinais.
- Parênquima pulmonar com atenuação preservada.
- Ausência de derrame pleural.
Abdome total:
- Fígado, pâncreas, baço, rins, aorta, adrenais e bexiga sem particularidades.
- Ausência de linfonodomegalias abdominais ou de líquido livre.',
    '{"indicacao": "", "tecnica": "Imagens obtidas antes e após a administração do meio de contraste intravenoso. Análise realizada segundo o protocolo RECIST 1.1 (Response Evaluation Criteria in Solid Tumors).", "achados": "XXXX Estudo base.\\nXXXX Exame comparado ao estudo de base realizado em:\\nXXXX Estudo anterior disponível para comparação realizado em:\\nLesões-alvo:\\n⚠️⚠️ Máx. 5 lesões, até 2 por órgão; (i) órgãos > 1,0 cm no MAIOR eixo e (ii) linfonodos > 1,5 cm no MENOR eixo ⚠️⚠️\\n1.\\n2.\\n3.\\n4.\\n5.\\nSomatória das lesões-alvo:\\nLesões não-alvo:\\n-\\nLesões novas:\\nXXX Estudo base (inicial).\\nXXX Não há evidências de novas lesões.\\nXXX Surgiu lesão ___.\\nDemais achados:\\nFace e pescoço:\\nFaringe, laringe, órbitas e cavidades paranasais sem particularidades.\\nGlândulas parótidas, submandibulares e tireoide sem particularidades.\\nNão se evidenciam linfonodomegalias.\\nTórax:\\n- Não se observam linfonodomegalias mediastinais.\\n- Parênquima pulmonar com atenuação preservada.\\n- Ausência de derrame pleural.\\nAbdome total:\\n- Fígado, pâncreas, baço, rins, aorta, adrenais e bexiga sem particularidades.\\n- Ausência de linfonodomegalias abdominais ou de líquido livre.", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia de Face, Pescoço, Tórax, Abdome Total Laudo Estruturado – Recist'
    AND ativo = 1
);

-- [116] Tomografia de Face, Tórax, Abdome Total Laudo Estruturado – Recist
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia de Face, Tórax, Abdome Total Laudo Estruturado – Recist',
    'TC',
    'XXXX Estudo base.
XXXX Exame comparado ao estudo de base realizado em:
XXXX Estudo anterior disponível para comparação realizado em:
Lesões-alvo:
⚠️⚠️ Máx. 5 lesões, até 2 por órgão; (i) órgãos > 1,0 cm no MAIOR eixo e (ii) linfonodos > 1,5 cm no MENOR eixo ⚠️⚠️
1.
2.
3.
4.
5.
Somatória das lesões-alvo:
Lesões não-alvo:
-
Lesões novas:
XXX Estudo base (inicial).
XXX Não há evidências de novas lesões.
XXX Surgiu lesão ___.
Demais achados:
Face:
Rinofaringe, orofaringe, órbitas e cavidades paranasais sem particularidades.
Glândulas parótidas e submandibulares sem particularidades.
Não se evidenciam linfonodomegalias.
Tórax:
- Não se observam linfonodomegalias mediastinais.
- Parênquima pulmonar com atenuação preservada.
- Ausência de derrame pleural.
Abdome total:
- Fígado, pâncreas, baço, rins, aorta, adrenais e bexiga sem particularidades.
- Ausência de linfonodomegalias abdominais ou de líquido livre.',
    '{"indicacao": "", "tecnica": "Imagens obtidas antes e após a administração do meio de contraste intravenoso. Análise realizada segundo o protocolo RECIST 1.1 (Response Evaluation Criteria in Solid Tumors).", "achados": "XXXX Estudo base.\\nXXXX Exame comparado ao estudo de base realizado em:\\nXXXX Estudo anterior disponível para comparação realizado em:\\nLesões-alvo:\\n⚠️⚠️ Máx. 5 lesões, até 2 por órgão; (i) órgãos > 1,0 cm no MAIOR eixo e (ii) linfonodos > 1,5 cm no MENOR eixo ⚠️⚠️\\n1.\\n2.\\n3.\\n4.\\n5.\\nSomatória das lesões-alvo:\\nLesões não-alvo:\\n-\\nLesões novas:\\nXXX Estudo base (inicial).\\nXXX Não há evidências de novas lesões.\\nXXX Surgiu lesão ___.\\nDemais achados:\\nFace:\\nRinofaringe, orofaringe, órbitas e cavidades paranasais sem particularidades.\\nGlândulas parótidas e submandibulares sem particularidades.\\nNão se evidenciam linfonodomegalias.\\nTórax:\\n- Não se observam linfonodomegalias mediastinais.\\n- Parênquima pulmonar com atenuação preservada.\\n- Ausência de derrame pleural.\\nAbdome total:\\n- Fígado, pâncreas, baço, rins, aorta, adrenais e bexiga sem particularidades.\\n- Ausência de linfonodomegalias abdominais ou de líquido livre.", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia de Face, Tórax, Abdome Total Laudo Estruturado – Recist'
    AND ativo = 1
);

-- [117] Tomografia de Tórax, Abdome Total Laudo Estruturado – Recist
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia de Tórax, Abdome Total Laudo Estruturado – Recist',
    'TC',
    'XXXX Estudo base.
XXXX Exame comparado ao estudo de base realizado em:
XXXX Estudo anterior disponível para comparação realizado em:
Lesões-alvo:
⚠️⚠️ Máx. 5 lesões, até 2 por órgão; (i) órgãos > 1,0 cm no MAIOR eixo e (ii) linfonodos > 1,5 cm no MENOR eixo ⚠️⚠️
1.
2.
3.
4.
5.
Somatória das lesões-alvo:
Lesões não-alvo:
-
Lesões novas:
XXX Estudo base (inicial).
XXX Não há evidências de novas lesões.
XXX Surgiu lesão ___.
Demais achados:
Tórax:
- Não se observam linfonodomegalias mediastinais.
- Parênquima pulmonar com atenuação preservada.
- Ausência de derrame pleural.
Abdome total:
- Fígado, pâncreas, baço, rins, aorta, adrenais e bexiga sem particularidades.
- Ausência de linfonodomegalias abdominais ou de líquido livre.',
    '{"indicacao": "", "tecnica": "Imagens obtidas antes e após a administração do meio de contraste intravenoso. Análise realizada segundo o protocolo RECIST 1.1 (Response Evaluation Criteria in Solid Tumors).", "achados": "XXXX Estudo base.\\nXXXX Exame comparado ao estudo de base realizado em:\\nXXXX Estudo anterior disponível para comparação realizado em:\\nLesões-alvo:\\n⚠️⚠️ Máx. 5 lesões, até 2 por órgão; (i) órgãos > 1,0 cm no MAIOR eixo e (ii) linfonodos > 1,5 cm no MENOR eixo ⚠️⚠️\\n1.\\n2.\\n3.\\n4.\\n5.\\nSomatória das lesões-alvo:\\nLesões não-alvo:\\n-\\nLesões novas:\\nXXX Estudo base (inicial).\\nXXX Não há evidências de novas lesões.\\nXXX Surgiu lesão ___.\\nDemais achados:\\nTórax:\\n- Não se observam linfonodomegalias mediastinais.\\n- Parênquima pulmonar com atenuação preservada.\\n- Ausência de derrame pleural.\\nAbdome total:\\n- Fígado, pâncreas, baço, rins, aorta, adrenais e bexiga sem particularidades.\\n- Ausência de linfonodomegalias abdominais ou de líquido livre.", "impressao": "", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia de Tórax, Abdome Total Laudo Estruturado – Recist'
    AND ativo = 1
);

-- [118] Tomografia Computadorizada da Região Axilar Direita
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada da Região Axilar Direita',
    'TC',
    'Estruturas ósseas de formato habitual, sem sinais de fraturas ou lesões ósseas focais com características agressivas.
Articulação acromioclavicular de contornos regulares.
Articulação glenoumeral congruente e de contornos regulares. Não foi identificado derrame articular significativo.
Planos musculares apresentam trofismo preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas sem a administração endovenosa de contraste iodado.", "achados": "Estruturas ósseas de formato habitual, sem sinais de fraturas ou lesões ósseas focais com características agressivas.\\nArticulação acromioclavicular de contornos regulares.\\nArticulação glenoumeral congruente e de contornos regulares. Não foi identificado derrame articular significativo.\\nPlanos musculares apresentam trofismo preservado.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada da Região Axilar Direita'
    AND ativo = 1
);

-- [119] Tomografia Computadorizada das Regiões Glúteas (direita e Esquerda)
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada das Regiões Glúteas (direita e Esquerda)',
    'TC',
    'Estruturas ósseas de formato habitual, sem sinais de fraturas.
Articulações femoroacetabulares de contornos regulares. Não foi identificado derrame articular significativo.
Planos musculares apresentam trofismo preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas sem a administração endovenosa de contraste iodado.", "achados": "Estruturas ósseas de formato habitual, sem sinais de fraturas.\\nArticulações femoroacetabulares de contornos regulares. Não foi identificado derrame articular significativo.\\nPlanos musculares apresentam trofismo preservado.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada das Regiões Glúteas (direita e Esquerda)'
    AND ativo = 1
);

-- [120] Tomografia Computadorizada da Articulação Sacroilíaca Direita
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada da Articulação Sacroilíaca Direita',
    'TC',
    'Articulação sacroilíaca com contornos regulares. Não se observam erosões corticais.
Peças sacrais e coccígeas com alinhamento e textura conservadas.
Ausência de fraturas ou de lesões ósseas focais com características agressivas.
Canal sacral sem estenose significativa.
Ventre muscular do piriforme com trofismo preservado.',
    '{"indicacao": "", "tecnica": "Imagens obtidas sem a administração endovenosa de contraste iodado.", "achados": "Articulação sacroilíaca com contornos regulares. Não se observam erosões corticais.\\nPeças sacrais e coccígeas com alinhamento e textura conservadas.\\nAusência de fraturas ou de lesões ósseas focais com características agressivas.\\nCanal sacral sem estenose significativa.\\nVentre muscular do piriforme com trofismo preservado.", "impressao": "Estruturas avaliadas de aspecto preservado.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada da Articulação Sacroilíaca Direita'
    AND ativo = 1
);

-- [121] Tomografia Computadorizada de Abdome e Pelve com Ênfase no Aparelho Urinário
INSERT INTO cop_templates
    (tenant_id, user_id, nome, modalidade, corpo, estrutura_json, publico, ativo, uso_count, created_at, updated_at)
SELECT
    @tenant_id,
    @user_id,
    'Tomografia Computadorizada de Abdome e Pelve com Ênfase no Aparelho Urinário',
    'TC',
    'Rins em localização habitual, com dimensões normais, forma e contornos regulares, parênquima de espessura preservada.
Não se observam cálculos renais ou hidronefrose.
Boa concentração e eliminação do meio de contraste por ambos os rins.
Artérias renais pérvias e com calibre preservado.
Veias renais pérvias e com anatomia habitual.
Ureteres com trajeto e calibre preservados, sem cálculos.
Bexiga com boa repleção, paredes finas e regulares, conteúdo homogêneo.
A análise sucinta das demais estruturas abdominopélvicas não revela alterações.',
    '{"indicacao": "", "tecnica": "Imagens obtidas antes e após a administração endovenosa de meio de contraste iodado.", "achados": "Rins em localização habitual, com dimensões normais, forma e contornos regulares, parênquima de espessura preservada.\\nNão se observam cálculos renais ou hidronefrose.\\nBoa concentração e eliminação do meio de contraste por ambos os rins.\\nArtérias renais pérvias e com calibre preservado.\\nVeias renais pérvias e com anatomia habitual.\\nUreteres com trajeto e calibre preservados, sem cálculos.\\nBexiga com boa repleção, paredes finas e regulares, conteúdo homogêneo.\\nA análise sucinta das demais estruturas abdominopélvicas não revela alterações.", "impressao": "Exame dentro dos padrões da normalidade.", "recomendacao": ""}',
    @publico,
    1,
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM cop_templates
    WHERE tenant_id = @tenant_id
    AND nome = 'Tomografia Computadorizada de Abdome e Pelve com Ênfase no Aparelho Urinário'
    AND ativo = 1
);

-- Verificação final
SELECT COUNT(*) AS templates_importados FROM cop_templates WHERE modalidade = 'TC' AND tenant_id = @tenant_id;
