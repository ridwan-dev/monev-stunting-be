SELECT b.id AS idro,
    b.tahun,
    b.thang,
    b.kddept AS kementerian_kode,
    b.nmsoutput AS suboutput_nama,
    b.alokasi_total,
    b.kdtema,
    b.sat,
    b.kdprogram AS program_kode,
    b.kdoutput AS output_kode,
    b.kdgiat AS kegiatan_kode,
    b.kdsoutput AS suboutput_kode,
    c.nmoutput AS output_nama,
    c.satuan AS satuan_output,
    c.alokasi_total AS alokasi_totaloutput,
    c.lokasi,
    d.nmgiat AS kegiatan_nama,
    d.nmunit,
    e.nmprogout,
    f.nmprogram AS program_nama,
    f.unit_kerja_eselon1,
    kl.kementerian_nama,
    kl.kementerian_nama_alias,
   FROM (((((renja.krisnarenja_t_soutput b
     LEFT JOIN renja.krisnarenja_t_output c ON (((b.parent_id = c.id) AND ((b.tahun)::text = (c.tahun)::text))))
     LEFT JOIN renja.krisnarenja_t_giat d ON (((c.parent_id = d.id) AND ((c.tahun)::text = (d.tahun)::text))))
     LEFT JOIN renja.krisnarenja_t_progout e ON (((d.parent_id = e.id) AND ((d.tahun)::text = (e.tahun)::text))))
     LEFT JOIN renja.krisnarenja_t_program f ON (((b.kdprogram = f.kdprogram) AND (b.kdunit = f.kdunit) AND (b.kddept = f.kddept) AND ((b.tahun)::text = (f.tahun)::text))))
     LEFT JOIN api.ref_kementerian kl ON ((b.kddept = (kl.kementerian_kode)::text)))
		 LEFT JOIN renja.krisnarenja_tagging tg ON (b.kode_ro = tg.kode_ro)
		 
  WHERE (b.kdtema ~~* '%008%'::text)