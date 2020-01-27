<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Absen extends CI_Controller {


	public function index()
	{
		if(empty($this->session->userdata('nama')))
		redirect('login','refresh');

		$date = '2020-01';
        $month = date('n', strtotime($date));
        $year = date('Y', strtotime($date));
        if ($month >= 1 && $month <= 9) {
            $yymm = $year . '-' . '0' . $month;
        } else {
            $yymm = $year . '-' . $month;
        }

        $num = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $data['num'] = $num;

		$data['pegawai'] = $this->db->get('pegawai')->result();

		// mencari libur umum
		$this->db->select('libur.*', FALSE);
        $this->db->from('libur');
        $this->db->like('tgl_libur', $yymm);
        $query_result = $this->db->get();
        $libur = $query_result->result();



		foreach ($data['pegawai'] as $ky => $v_pegawai) {
            $key = 1;
            $x = 0;

            for ($i = 1; $i <= $num; $i++) {

                if ($i >= 1 && $i <= 9) {
                    $tgl = $yymm . '-' . '0' . $i;
                } else {
                    $tgl = $yymm . '-' . $i;
                }

                // mencari nama hari ****************
                $nm_hari = date('l', strtotime("+$x days", strtotime($year . '-' . $month . '-' . $key)));

                // libur weekend **********************
                if ($nm_hari=='Sunday' || $nm_hari=='Saturday') {
                    $data['absen'][$ky][$i] = 'L';
                }

                // libur Nasional **********************
                if (!empty($libur)) {
                    foreach ($libur as $v) {
                        if ($v->tgl_libur == $tgl) {
                            $data['absen'][$ky][$i] = 'H';
                        }
                    }
                }
                   


                $this->db->select('*');
                $this->db->where('pegawai_id', $v_pegawai->admin_id);
                $this->db->where('tgl',$tgl);
                $qry = $this->db->get('absen');

                if(empty($data['absen'][$ky][$i])) {

	                if($qry->num_rows()>0) {
	                	$data['absen'][$ky][$i] = $qry->row()->status;
		            } else{
		            	$data['absen'][$ky][$i] = 'x';
		            }
	        	}
	            

	            $key++;
	            $flag = '';
            }

        }
		
		$this->load->view('v_header');
		// $this->load->view('v_array', $data);
		$this->load->view('v_absen', $data);

		$this->load->view('v_footer');
	}





}
?>

<table class="table table-bordered" style="width: 150%">
    <thead>
    <tr>
        <th style="width:20%">Nama</th>
        <?php for ($i = 1; $i <= $num; $i++) { ?>
            <th class="std_p"><?php echo $i ?></th>
        <?php } ?>

    </tr>

    </thead>

    <tbody>
    <?php foreach ($absen as $key => $v_absen): ?>
        <tr>
            <td><?php echo $pegawai[$key]->name ?></td>
            <?php foreach ($v_absen as $v_data): ?>
                <td><?php echo $v_data ?></td>
            <?php endforeach; ?>

        </tr>
    <?php endforeach; ?>
    </tbody>

</table>