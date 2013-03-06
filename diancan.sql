-- phpMyAdmin SQL Dump
-- version 3.4.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 26, 2012 at 09:10 AM
-- Server version: 5.1.30
-- PHP Version: 5.2.8

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `diancan`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '菜单id',
  `name` varchar(128) NOT NULL COMMENT '名称',
  `price` float NOT NULL COMMENT '价格',
  `r_id` int(11) NOT NULL COMMENT '所属餐馆id',
  `hit` int(11) DEFAULT '0' COMMENT '被点次数',
  `level` int(1) DEFAULT '0' COMMENT '评价等级',
  `lock` int(1) DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=35 ;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `name`, `price`, `r_id`, `hit`, `level`, `lock`) VALUES
(20, '葱香滑鸡 ', 10, 8, 0, 0, 0),
(21, '海砺煎 ', 10, 8, 0, 0, 0),
(22, '爆炒鱿鱼  ', 11, 8, 1, 0, 0),
(23, '肉丝海鲜菇 ', 10, 8, 1, 0, 0),
(24, '烤鸡', 10, 8, 1, 0, 0),
(25, '腐竹焖肉片', 10, 8, 1, 0, 0),
(26, '香酥鸡排 ', 10, 8, 0, 0, 0),
(27, '糖 醋 鸡 丁', 10, 8, 2, 0, 0),
(28, '水煮肉片 ', 10, 8, 0, 0, 0),
(29, '酱汁烧鸭', 10, 8, 0, 0, 0),
(30, '土豆肉丁  ', 10, 8, 1, 0, 0),
(31, '啤酒鸭 ', 10, 8, 1, 0, 0),
(32, '回锅肉', 10, 8, 0, 0, 0),
(33, '梅菜扣肉 ', 10, 8, 1, 0, 0),
(34, '美芹鱼卷 ', 10, 8, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单号',
  `r_id` int(11) NOT NULL COMMENT '餐厅编号',
  `m_id` int(11) NOT NULL COMMENT '餐单id',
  `u_id` int(11) NOT NULL COMMENT '用户id',
  `price` float NOT NULL COMMENT '价格',
  `ordertime` int(11) NOT NULL COMMENT '下单时间',
  `status` int(1) DEFAULT '0' COMMENT '订单状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=73 ;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `r_id`, `m_id`, `u_id`, `price`, `ordertime`, `status`) VALUES
(64, 8, 30, 24, 10, 1343268311, 8),
(65, 8, 22, 27, 11, 1343268363, 8),
(66, 8, 24, 22, 10, 1343268378, 8),
(67, 8, 27, 31, 10, 1343268423, 8),
(68, 8, 23, 33, 10, 1343268491, 8),
(69, 8, 33, 23, 10, 1343268500, 8),
(70, 8, 31, 34, 10, 1343269004, 8),
(71, 8, 25, 32, 10, 1343269314, 8),
(72, 8, 27, 35, 10, 1343269439, 8);

-- --------------------------------------------------------

--
-- Table structure for table `restaurant`
--

CREATE TABLE IF NOT EXISTS `restaurant` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '餐馆id',
  `name` varchar(128) NOT NULL COMMENT '名称',
  `phone` varchar(16) DEFAULT NULL COMMENT '电话',
  `addr` varchar(255) DEFAULT NULL COMMENT '地址',
  `level` int(1) DEFAULT '0' COMMENT '评价等级',
  `lock` int(1) DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `restaurant`
--

INSERT INTO `restaurant` (`id`, `name`, `phone`, `addr`, `level`, `lock`) VALUES
(8, '张氏便当', '15659298803', '', 7, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `name` varchar(32) CHARACTER SET utf8 NOT NULL COMMENT '用户名',
  `password` varchar(64) CHARACTER SET utf8 NOT NULL COMMENT '密码',
  `qq` int(10) DEFAULT NULL COMMENT 'QQ',
  `money` float NOT NULL COMMENT '余额',
  `ip` varchar(15) CHARACTER SET utf8 DEFAULT NULL COMMENT '上次登录ip',
  `lasttime` int(11) DEFAULT NULL COMMENT '上次登录时间',
  `logincounts` int(11) DEFAULT '0' COMMENT '登录次数',
  `lock` int(1) NOT NULL DEFAULT '0' COMMENT '账户状态',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT '权限级别',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=38 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `password`, `qq`, `money`, `ip`, `lasttime`, `logincounts`, `lock`, `level`) VALUES
(1, 'admin', 'admin123', 666, -7, '192.168.1.64', 1343293739, 73, 0, 9),
(2, 'test', 'test123', 1234223, 25.5, '192.168.1.64', 1343183180, 6, 0, 0),
(20, '王燕芬', '9963192', 547893861, 50, '192.168.1.32', 1343195624, 1, 0, 0),
(21, '林灿辉', 'dandan..', 594844394, 50, '192.168.1.18', 1343268261, 2, 0, 0),
(22, '黄丽婷', 'huang8888', 1062162860, 40, '192.168.1.56', 1343268244, 2, 0, 0),
(23, '陈雪敏', '123456', 0, 40, '192.168.1.33', 1343268282, 2, 0, 0),
(24, 'maomaochong', 'chen890125', 121416722, 40, '192.168.1.61', 1343271166, 3, 0, 0),
(25, '徐金波', 'xujinbo', 871734390, 50, '192.168.1.14', 1343268587, 2, 0, 0),
(26, 'leilingming', '123456', 184265722, 50, '192.168.1.101', 1343196414, 4, 0, 0),
(27, '谢诗琪', 'candy', 31406483, 39, '192.168.1.36', 1343268344, 2, 0, 0),
(28, '黄慧丹', 'admin123', 79777134, 50, '192.168.1.72', 1343196419, 1, 0, 0),
(37, '王健', 'user123', 391825394, 0, '192.168.1.64', 1343275428, 1, 0, 0),
(29, '张伟泽', '321321', 165481164, 50, '192.168.1.68', 1343268258, 2, 0, 0),
(30, 'yujunfei', '123321', 3027221, 50, '192.168.1.17', 1343207043, 1, 0, 0),
(31, '陈少华', '520hua.870925', 1652634169, 40, '192.168.1.21', 1343268271, 1, 0, 0),
(32, '林伟锋', '3331234', 719379854, 40, '192.168.1.58', 1343268521, 1, 0, 0),
(33, '李霞', 'shenaisxy', 0, 40, '192.168.1.15', 1343268460, 1, 0, 0),
(34, '林鹰辉', '880416', 1543775176, 40, '192.168.1.71', 1343268968, 1, 0, 0),
(35, '吴梅梅', 'zfphjhwhcm198726', 514555416, 40, '192.168.1.19', 1343269351, 1, 0, 0),
(36, '黄娇', '5623485', 466872350, 50, '192.168.1.91', 1343269423, 1, 0, 0);
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
