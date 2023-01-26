SELECT a.id,
    a.thang,
    a.tahun,
    a.parent_id,
    a.kddept AS kementerian_kode,
    concat(a.kddept, a.kdunit, a.kdprogram, a.kdgiat, a.kdoutput, a.kdsoutput, a.kdkmpnen) AS kode_ro,
    a.kdunit,
    a.kdprogram AS program_kode,
    a.kdgiat AS kegiatan_kode,
    a.kdoutput AS output_kode,
    a.kdsoutput AS suboutput_kode,
    a.kdkmpnen AS komponen_kode,
    a.nmkmpnen AS komponen_nama,
    a.jenis_komponen,
    a.indikator_pbj,
    a.indikator_komponen,
    a.satuan,
    g.alokasi_0,
    g.alokasi_1,
    g.alokasi_2,
    g.alokasi_3,
    g.target_0,
    g.target_1,
    g.target_2,
    g.target_3,
    b.id AS idro,
    b.nmsoutput AS suboutput_nama,
    b.alokasi_total,
    b.kdtema,
    b.sat,
    c.nmoutput AS output_nama,
    c.satuan AS satuan_output,
    c.alokasi_total AS alokasi_totaloutput,
    c.lokasi,
    d.nmgiat AS kegiatan_nama,
    d.nmunit,
    e.nmprogout,
    f.nmprogram AS program_nama,
    f.unit_kerja_eselon1,
    kl.nama AS kementerian_nama,
    kl.nama_pendek AS kementerian_nama_short
   FROM (((((((renja.krisnarenja_t_kmpnen a
     LEFT JOIN renja.krisnarenja_t_soutput b ON (((a.parent_id = b.id) AND (a.thang = b.thang))))
     LEFT JOIN renja.krisnarenja_t_output c ON (((b.parent_id = c.id) AND ((b.tahun)::text = (c.tahun)::text))))
     LEFT JOIN renja.krisnarenja_t_giat d ON (((c.parent_id = d.id) AND ((c.tahun)::text = (d.tahun)::text))))
     LEFT JOIN renja.krisnarenja_t_progout e ON (((d.parent_id = e.id) AND ((d.tahun)::text = (e.tahun)::text))))
     LEFT JOIN renja.krisnarenja_t_program f ON (((a.kdprogram = f.kdprogram) AND (a.kdunit = f.kdunit) AND (a.kddept = f.kddept) AND ((a.tahun)::text = (f.tahun)::text))))
     LEFT JOIN r_kementerian kl ON ((a.kddept = (kl.kode)::text)))
     LEFT JOIN renja.krisnarenja_t_alokasi g ON (((a.id = g.komponen_id) AND ((a.tahun)::text = (g.tahun)::text))))
  WHERE (b.kdtema ~~* '%008%'::text)